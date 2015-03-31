<?php
namespace Networkteam\SentryClient;

/***************************************************************
 *  (c) 2015 networkteam GmbH - all rights reserved
 ***************************************************************/

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Party\Domain\Model\Person;

/**
 * @Flow\Scope("singleton")
 */
class ErrorHandler {

	/**
	 * @Flow\Inject(setting="dsn", package="Networkteam.SentryClient")
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
	 * @param \Exception $exception The exception to capture
	 * @param array $extraData Additional data passed to the Sentry sample
	 */
	public function handleException(\Exception $exception, array $extraData = array()) {
		$this->setUserContext();

		$tags = array('code' => $exception->getCode());
		if ($exception instanceof \TYPO3\Flow\Exception) {
			$extraData['referenceCode'] = $exception->getReferenceCode();
		}

		$this->client->captureException($exception, array('extra' => $extraData, 'tags' => $tags));
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
}
