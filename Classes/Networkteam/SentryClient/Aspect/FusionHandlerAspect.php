<?php
namespace Networkteam\SentryClient\Aspect;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Core\ExceptionHandlers\AbsorbingHandler;

/**
 * @Flow\Aspect
 */
class FusionHandlerAspect {

	/**
	 * @Flow\Inject
	 * @var \Networkteam\SentryClient\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * Forward all exceptions that are handled in Fusion rendering exception handlers to Sentry
	 *
	 * Ignores the exception, if it was handled by an AbsorbingHandler.
	 *
	 * @Flow\After("within(Neos\Fusion\Core\ExceptionHandlers\AbstractRenderingExceptionHandler) && method(.*->handle())")
	 * @param \Neos\Flow\Aop\JoinPointInterface $joinPoint
	 */
	public function captureException(\Neos\Flow\Aop\JoinPointInterface $joinPoint) {
		if ($joinPoint->getProxy() instanceof AbsorbingHandler) {
			return;
		}
		$exception = $joinPoint->getMethodArgument('exception');
		$args = $joinPoint->getMethodArguments();
		$fusionPath = isset($args['fusionPath']) ? $args['fusionPath'] : $args['typoScriptPath'];
		$this->errorHandler->handleException($exception, array('fusionPath' => $fusionPath));
	}
}
