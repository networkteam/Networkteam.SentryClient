<?php

namespace Networkteam\SentryClient\Aspect;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Networkteam\SentryClient\ErrorHandler;

/**
 * @Flow\Aspect
 */
class FusionHandlerAspect
{
    /**
     * @Flow\Inject
     *
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * Forward all exceptions that are handled in Fusion rendering exception handlers to Sentry.
     *
     * @Flow\After("within(Neos\Fusion\Core\ExceptionHandlers\AbstractRenderingExceptionHandler) && method(.*->handle())")
     *
     * @param JoinPointInterface $joinPoint
     */
    public function captureException(JoinPointInterface $joinPoint)
    {
        $exception = $joinPoint->getMethodArgument('exception');
        $args = $joinPoint->getMethodArguments();
        $fusionPath = isset($args['fusionPath']) ? $args['fusionPath'] : $args['typoScriptPath'];
        $this->errorHandler->handleException($exception, array('fusionPath' => $fusionPath));
    }
}
