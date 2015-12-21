<?php
namespace Networkteam\SentryClient\Handler;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Object\ObjectManagerInterface;

class DebugExceptionHandler extends \TYPO3\Flow\Error\DebugExceptionHandler {

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionWeb($exception) {
		$this->sendExceptionToSentry($exception);
		parent::echoExceptionWeb($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionCLI($exception) {
		$this->sendExceptionToSentry($exception);
		parent::echoExceptionCLI($exception);
	}

	/**
	 * Send an exception to Sentry, but only if the "logException" rendering option is TRUE
	 *
	 * During compiletime there might be missing dependencies, so we need additional safeguards to
	 * not cause errors.
	 *
	 * @param \Exception $exception \Exception or \Throwable
	 */
	protected function sendExceptionToSentry($exception) {
		if (!Bootstrap::$staticObjectManager instanceof ObjectManagerInterface) {
			return;
		}
		$logException = isset($this->renderingOptions['logException']) && $this->renderingOptions['logException'];
		if ($logException) {
			try {
				$errorHandler = Bootstrap::$staticObjectManager->get('Networkteam\SentryClient\ErrorHandler');
				if ($errorHandler !== NULL) {
					$errorHandler->handleException($exception);
				}
			} catch (\Exception $exception) {
				// Quick'n dirty workaround to catch exception with the error handler is called during compile time
			}
		}
	}

}
