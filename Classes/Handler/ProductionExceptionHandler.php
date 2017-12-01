<?php

namespace Networkteam\SentryClient\Handler;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Networkteam\SentryClient\ErrorHandler;

class ProductionExceptionHandler extends \Neos\Flow\Error\ProductionExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    public function echoExceptionWeb($exception)
    {
        $this->sendExceptionToSentry($exception);
        parent::echoExceptionWeb($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function echoExceptionCLI($exception)
    {
        $this->sendExceptionToSentry($exception);
        parent::echoExceptionCLI($exception);
    }

    /**
     * Send an exception to Sentry, but only if the "logException" rendering option is TRUE.
     *
     * During compiletime there might be missing dependencies, so we need additional safeguards to
     * not cause errors.
     *
     * @param object $exception \Exception or \Throwable
     */
    protected function sendExceptionToSentry($exception)
    {
        if (!Bootstrap::$staticObjectManager instanceof ObjectManagerInterface) {
            return;
        }

        $options = $this->resolveCustomRenderingOptions($exception);
        if (isset($options['logException']) && $options['logException']) {
            try {
                /** @var ErrorHandler $errorHandler */
                $errorHandler = Bootstrap::$staticObjectManager->get(ErrorHandler::class);
                if ($errorHandler !== null) {
                    $errorHandler->handleException($exception);
                }
            } catch (\Exception $exception) {
                // Quick'n dirty workaround to catch exception with the error handler is called during compile time
            }
        }
    }
}
