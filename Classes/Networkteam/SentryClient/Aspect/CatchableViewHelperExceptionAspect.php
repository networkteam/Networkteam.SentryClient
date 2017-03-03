<?php
namespace Networkteam\SentryClient\Aspect;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class CatchableViewHelperExceptionAspect {

	/**
	 * @Flow\Inject
	 * @var \Networkteam\SentryClient\ErrorHandler
	 */
	protected $errorHandler;

	/**
	 * @Flow\AfterThrowing("within(TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper) && method(.*->render())")
	 * @param \Neos\Flow\Aop\JoinPoint $joinPoint
	 */
	public function catchException(\Neos\Flow\Aop\JoinPoint $joinPoint) {
		$exception = $joinPoint->getException();
		$this->errorHandler->handleException($exception);
	}
}