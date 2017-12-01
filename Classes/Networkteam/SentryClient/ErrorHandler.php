<?php
namespace Networkteam\SentryClient;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Reflection\ReflectionService;

use Networkteam\SentryClient\ClientConfigurator;

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
	 * @var \Networkteam\SentryClient\Context\UserContextServiceInterface
	 * @Flow\Inject
	 */
	protected $userContextService;

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Initialize/return the raven client and fatal error handler (shutdown function)
	 *
	 * @return null|\Raven_Client
	 */
	public function getClient() {
		if (empty($this->dsn)) {
			return null;
		}

		if (!empty($this->client)) {
			return $this->client;
		}

		$client = new \Raven_Client($this->dsn);
		$errorHandler = new \Raven_ErrorHandler($client, true);
		$errorHandler->registerShutdownFunction();
		$this->client = $client;

		$this->setTagsContext();

		$clientConfiguratorImplementations = $this->reflectionService->getAllImplementationClassNamesForInterface(ClientConfigurator\ClientConfiguratorInterface::class);
		foreach ($clientConfiguratorImplementations as $clientConfiguratorImplementationClassName) {
			$this->objectManager->get($clientConfiguratorImplementationClassName)->configureClient($client);
		}

		return $this->client;
	}

	/**
	 * Explicitly handle an exception, should be called from an exception handler (in Flow or TypoScript)
	 *
	 * @param object $exception The exception to capture
	 * @param array $extraData Additional data passed to the Sentry sample
	 */
	public function handleException($exception, array $extraData = array()) {
		if (!$this->getClient() instanceof \Raven_Client) {
			return;
		}

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
		if ($exception instanceof \Neos\Flow\Exception) {
			$extraData['referenceCode'] = $exception->getReferenceCode();
		}

		$this->getClient()->captureException($exception, array(
				'message' => $exception->getMessage(),
				'extra' => $extraData,
				'tags' => $tags)
		);
	}

	/**
	 * Set tags on the raven context
	 */
	protected function setTagsContext() {
		$objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
		/** @var \Neos\Flow\Utility\Environment $environment */
		$environment = $objectManager->get('Neos\Flow\Utility\Environment');

		$tags = array(
			'php_version' => phpversion(),
			'flow_context' => (string)$environment->getContext(),
			'flow_version' => FLOW_VERSION_BRANCH
		);

		$this->getClient()->tags_context($tags);
	}

	/**
	 * Set user information on the raven context
	 */
	protected function setUserContext() {
		$objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
		/** @var \Neos\Flow\Security\Context $securityContext */
		$securityContext = $objectManager->get('Neos\Flow\Security\Context');

		$userContext = array();

		if ($securityContext->isInitialized()) {
			$account = $securityContext->getAccount();
			if ($account !== NULL) {
				$userContext['username'] = $account->getAccountIdentifier();
			}
			$externalUserContextData = $this->userContextService->getUserContext($securityContext);
			if ($externalUserContextData !== []) {
				$userContext = array_merge($userContext, $externalUserContextData);
			}
		}

		if ($userContext !== array()) {
			$this->getClient()->user_context($userContext);
		}
	}

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->dsn = isset($settings['dsn']) ? $settings['dsn']: '';
	}
}
