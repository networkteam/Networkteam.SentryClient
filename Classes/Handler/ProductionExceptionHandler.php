<?php
namespace Networkteam\SentryClient\Handler;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Error\ProductionExceptionHandler as ProductionExceptionHandlerBase;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Networkteam\SentryClient\ErrorHandler;

class ProductionExceptionHandler extends ProductionExceptionHandlerBase
{

    /**
     * Handles the given exception
     *
     * @param \Throwable $exception The exception object
     * @return void
     */
    public function handleException($exception)
    {
        // Ignore if the error is suppressed by using the shut-up operator @
        if (error_reporting() === 0) {
            return;
        }

        $this->renderingOptions = $this->resolveCustomRenderingOptions($exception);
        $eventId = $this->sendExceptionToSentry($exception);

        $exceptionWasLogged = false;
        if ($this->throwableStorage instanceof ThrowableStorageInterface && isset($this->renderingOptions['logException']) && $this->renderingOptions['logException']) {
            $message = $this->throwableStorage->logThrowable($exception, ['sentryEventId' => $eventId]);
            $this->logger->critical($message);
            $exceptionWasLogged = true;
        }

        if (PHP_SAPI === 'cli') {
            $this->echoExceptionCli($exception, $exceptionWasLogged);
        }

        $this->echoExceptionWeb($exception);
    }

    /**
     * Send an exception to Sentry, but only if the "logException" rendering option is TRUE
     *
     * During compiletime there might be missing dependencies, so we need additional safeguards to
     * not cause errors.
     *
     * @param \Throwable $exception The throwable
     * @return string EventId
     */
    protected function sendExceptionToSentry($exception): ?string
    {
        if (!Bootstrap::$staticObjectManager instanceof ObjectManagerInterface) {
            return null;
        }

        $logException = $this->renderingOptions['logException'] ?? false;
        $sentryClientIgnoreException = $this->renderingOptions['sentryClientIgnoreException'] ?? false;
        if ($logException && !$sentryClientIgnoreException) {
            try {
                $errorHandler = Bootstrap::$staticObjectManager->get(ErrorHandler::class);
                if ($errorHandler !== null) {
                    return $errorHandler->handleException($exception);
                }
            } catch (\Exception $exception) {
                // Quick'n dirty workaround to catch exception with the error handler is called during compile time
            }
        }
        return null;
    }
}
