<?php

namespace Networkteam\SentryClient;

use Jenssegers\Agent\Agent;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Utility\Environment;
use function Sentry\captureException;
use function Sentry\configureScope;
use function Sentry\init as initSentry;
use Sentry\State\Hub;
use Sentry\State\Scope;

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
        initSentry(['dsn' => $this->dsn]);
        $this->agent = new Agent();
    }

    /**
     * Explicitly handle an exception, should be called from an exception handler (in Flow or TypoScript)
     *
     * @param object $exception The exception to capture
     * @param array $extraData Additional data passed to the Sentry sample
     */
    public function handleException($exception, array $extraData = []): void
    {
        if (!$exception instanceof \Throwable) {
            // can`t handle anything different from \Exception and \Throwable
            return;
        }

        if ($exception instanceof \Neos\Flow\Exception) {
            $extraData['referenceCode'] = $exception->getReferenceCode();
        }

        $this->setExtraContext($extraData);
        $this->setUserContext();
        $this->setTagsContext(['code' => $exception->getCode()]);
        $this->setReleaseContext();

        captureException($exception);
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
     * Set extra on the sentry event scope
     */
    protected function setExtraContext(array $additionalExtraData): void
    {
        $data = array_merge_recursive($additionalExtraData, $this->getBrowserContext(), $this->getOsContext());

        configureScope(function (Scope $scope) use ($data): void {
            foreach ($data as $key => $value) {
                $scope->setExtra($key, $value);
            }
        });
    }

    /**
     * Set tags on the sentry event scope
     * @param array $additionalTags
     */
    protected function setTagsContext(array $additionalTags): void
    {
        $objectManager = Bootstrap::$staticObjectManager;
        $environment = $objectManager->get(Environment::class);
        $tags = [
            'php_version' => PHP_VERSION,
            'flow_context' => (string)$environment->getContext(),
            'flow_version' => FLOW_VERSION_BRANCH
        ];
        $tags = array_merge($tags, $additionalTags);

        configureScope(function (Scope $scope) use ($tags): void {
            foreach ($tags as $tagKey => $tagValue) {
                $scope->setTag($tagKey, $tagValue);
            }
        });
    }

    /**
     * Set user information on the sentry event scope
     */
    protected function setUserContext(): void
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

        configureScope(function (Scope $scope) use ($userContext): void {
            if ($userContext !== []) {
                $scope->setUser($userContext);
            }
        });
    }

    /**
     * Set release information as client option
     */
    protected function setReleaseContext(): void
    {
        $client = Hub::getCurrent()->getClient();
        if ($this->release !== '' && $client) {
            $options = $client->getOptions();
            $options->setRelease($this->release);
        }
    }

    /**
     * @param array $settings
     */
    public function injectSettings(array $settings)
    {
        $this->dsn = $settings['dsn'] ?? '';
        $this->release = $settings['release'] ?? '';
    }
}
