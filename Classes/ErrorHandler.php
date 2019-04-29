<?php
namespace Networkteam\SentryClient;

use Jenssegers\Agent\Agent;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Utility\Environment;

/**
 * @Flow\Scope("singleton")
 */
class ErrorHandler
{

    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var string
     */
    protected $release;

    /**
     * @var \Raven_Client
     */
    protected $client;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * @Flow\Inject
     * @var \Networkteam\SentryClient\Context\UserContextServiceInterface
     */
    protected $userContextService;

    /**
     * Initialize the raven client and fatal error handler (shutdown function)
     */
    public function initializeObject()
    {
        $client = new \Raven_Client($this->dsn);
        $errorHandler = new \Raven_ErrorHandler($client, true);
        $errorHandler->registerShutdownFunction();
        $this->client = $client;
        $this->agent = new Agent();

        $this->setTagsContext();
    }

    /**
     * Explicitly handle an exception, should be called from an exception handler (in Flow or TypoScript)
     *
     * @param object $exception The exception to capture
     * @param array $extraData Additional data passed to the Sentry sample
     */
    public function handleException($exception, array $extraData = [])
    {
        if (!$this->client instanceof \Raven_Client) {
            return;
        }

        if (!$exception instanceof \Throwable) {
            // can`t handle anything different from \Exception and \Throwable
            return;
        }

        $this->setUserContext();

        $tags = ['code' => $exception->getCode()];
        if ($exception instanceof \Neos\Flow\Exception) {
            $extraData['referenceCode'] = $exception->getReferenceCode();
        }

        $data = [
            'message' => $exception->getMessage(),
            'extra' => $extraData,
            'tags' => $tags,
        ];
        $data = array_merge_recursive($data, $this->getBrowserContext(), $this->getOsContext());

        $this->client->captureException($exception, $data);
    }

    protected function getBrowserContext(): array
    {
        return [
            'contexts' => [
                'browser' => [
                    'name' => $this->agent->browser(),
                    'version' => $this->agent->version($this->agent->browser())
                ]
            ]
        ];
    }

    protected function getOsContext(): array
    {
        return [
            'contexts' => [
                'os' => [
                    'name' => $this->agent->platform(),
                    'version' => $this->agent->version($this->agent->platform())
                ]
            ]
        ];
    }

    /**
     * Set tags on the raven context
     */
    protected function setTagsContext()
    {
        $objectManager = Bootstrap::$staticObjectManager;
        $environment = $objectManager->get(Environment::class);

        $tags = [
            'php_version' => phpversion(),
            'flow_context' => (string)$environment->getContext(),
            'flow_version' => FLOW_VERSION_BRANCH
        ];

        $this->client->tags_context($tags);
    }

    /**
     * Set user information on the raven context
     */
    protected function setUserContext()
    {
        $objectManager = Bootstrap::$staticObjectManager;
        $securityContext = $objectManager->get(SecurityContext::class);

        $userContext = [];

        if ($securityContext->isInitialized()) {
            $account = $securityContext->getAccount();
            if ($account !== null) {
                $userContext['username'] = $account->getAccountIdentifier();
            }
            $externalUserContextData = $this->userContextService->getUserContext($securityContext);
            if ($externalUserContextData !== []) {
                $userContext = array_merge($userContext, $externalUserContextData);
            }
        }

        if ($userContext !== []) {
            $this->client->user_context($userContext);
        }

        if ($this->release !== '') {
            $this->client->setRelease($this->release);
        }
    }

    /**
     * @param array $settings
     */
    public function injectSettings(array $settings)
    {
        $this->dsn = $settings['dsn'] ?? '';
        $this->release = isset($settings['release']) ? $settings['release'] : '';
    }

    /**
     * @return \Raven_Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
