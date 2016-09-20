<?php
namespace Networkteam\SentryClient;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Party\Domain\Model\Person;

/**
 * @Flow\Scope("singleton")
 */
class ErrorHandler {

	/**
	 * @var string
	 */
	protected $dsn;

	/**
	 * @var \Raven_Client
	 */
	protected $client;

	/**
	 * Initialize the raven client and fatal error handler (shutdown function)
	 */
	public function initializeObject() {
		$client = new \Raven_Client($this->dsn);
		$errorHandler = new \Raven_ErrorHandler($client, TRUE);
		$errorHandler->registerShutdownFunction();
		$this->client = $client;

		$this->setTagsContext();
	}

	/**
	 * Explicitly handle an exception, should be called from an exception handler (in Flow or TypoScript)
	 *
	 * @param object $exception The exception to capture
	 * @param array $extraData Additional data passed to the Sentry sample
	 */
	public function handleException($exception, array $extraData = array()) {

		if (!$exception instanceof \Exception) {
			if ($exception instanceof \Throwable) {
				$mappedException = new \Exception($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
				$extraData['file'] = $exception->getFile();
				$extraData['line'] = $exception->getLine();
				$extraData['traceString'] = $exception->getTraceAsString();
				$extraData['original'] = 'Remapped from \Throwable';
				$exception = $mappedException;
			} else {
				// can`t handle anything different from \Exception and \Throwable
				return;
			}
		}

		$this->setUserContext();

		$tags = array('code' => $exception->getCode());
		if ($exception instanceof \TYPO3\Flow\Exception) {
			$extraData['referenceCode'] = $exception->getReferenceCode();
		}

		$this->client->captureException($exception, array(
				'message' => $exception->getMessage(),
				'extra' => $extraData,
				'tags' => $tags)
		);
	}

	/**
	 * Set tags on the raven context
	 */
	protected function setTagsContext() {
		$objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager;
		/** @var \TYPO3\Flow\Utility\Environment $environment */
		$environment = $objectManager->get('TYPO3\Flow\Utility\Environment');

		$tags = array(
			'php_version' => phpversion(),
			'flow_context' => (string)$environment->getContext(),
			'flow_version' => FLOW_VERSION_BRANCH
		);

		$this->client->tags_context($tags);
	}

	/**
	 * Set user information on the raven context
	 */
	protected function setUserContext() {
		$objectManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager;
		/** @var \TYPO3\Flow\Security\Context $securityContext */
		$securityContext = $objectManager->get('TYPO3\Flow\Security\Context');

		$userContext = array();

		if ($securityContext->isInitialized()) {
			$account = $securityContext->getAccount();
			if ($account !== NULL) {
				$userContext['username'] = $account->getAccountIdentifier();
			}
			$party = $securityContext->getParty();
			if ($party instanceof Person && $party->getPrimaryElectronicAddress() !== NULL) {
				$userContext['email'] = (string)$party->getPrimaryElectronicAddress();
			} elseif ($party !== NULL && ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
				$userContext['email'] = (string)ObjectAccess::getProperty($party, 'emailAddress');
			}
		}

		if ($userContext !== array()) {
			$this->client->user_context($userContext);
		}
	}

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->dsn = isset($settings['dsn']) ? $settings['dsn']: '';
	}

	/**
	 * @return \Raven_Client
	 */
	public function getClient() {
		return $this->client;
	}
}
