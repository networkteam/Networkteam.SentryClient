<?php
namespace Networkteam\SentryClient\Aspect;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class TypoScriptHandlerAspect {

	/**
	 * @Flow\Inject
	 * @var \Networkteam\SentryClient\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * Forward all exceptions that are handled in TypoScript rendering exception handlers to Sentry
	 *
	 * @Flow\After("within(Neos\Fusion\Core\ExceptionHandlers\AbstractRenderingExceptionHandler) && method(.*->handle())")
	 * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint
	 */
	public function captureException(\Neos\Flow\Aop\JoinPointInterface $joinPoint) {
		$exception = $joinPoint->getMethodArgument('exception');
		$args =$joinPoint->getMethodArguments();
		$fusionPath = isset($args['fusionPath']) ? $args['fusionPath'] : $args['typoScriptPath'];
		$this->errorHandler->handleException($exception, array('fusionPath' => $fusionPath));
	}
}