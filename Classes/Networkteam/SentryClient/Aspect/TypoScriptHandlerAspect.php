<?php
namespace Networkteam\SentryClient\Aspect;

use TYPO3\Flow\Annotations as Flow;

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
	 * @Flow\After("within(TYPO3\TypoScript\Core\ExceptionHandlers\AbstractRenderingExceptionHandler) && method(.*->handle())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 */
	public function captureException(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$exception = $joinPoint->getMethodArgument('exception');
		$this->errorHandler->handleException($exception, array('typoScriptPath' => $joinPoint->getMethodArgument('typoScriptPath')));
	}
}