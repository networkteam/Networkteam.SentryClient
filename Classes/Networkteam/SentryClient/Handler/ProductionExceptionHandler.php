<?php
namespace Networkteam\SentryClient\Handler;

/***************************************************************
 *  (c) 2015 networkteam GmbH - all rights reserved
 ***************************************************************/

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;

class ProductionExceptionHandler extends \TYPO3\Flow\Error\ProductionExceptionHandler {

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionWeb(\Exception $exception) {
		$this->sendExceptionToSentry($exception);
		parent::echoExceptionWeb($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionCLI(\Exception $exception) {
		$this->sendExceptionToSentry($exception);
		parent::echoExceptionCLI($exception);
	}

	/**
	 * @param \Exception $exception
	 */
	protected function sendExceptionToSentry(\Exception $exception) {
		if (!Bootstrap::$staticObjectManager instanceof ObjectManagerInterface) {
			return;
		}
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