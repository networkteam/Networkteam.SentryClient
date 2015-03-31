<?php
namespace Networkteam\SentryClient\Handler;

/***************************************************************
 *  (c) 2015 networkteam GmbH - all rights reserved
 ***************************************************************/

use TYPO3\Flow\Annotations as Flow;

class ProductionExceptionHandler extends \TYPO3\Flow\Error\ProductionExceptionHandler {

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionWeb(\Exception $exception) {
		$errorHandler = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('Networkteam\SentryClient\ErrorHandler');
		if ($errorHandler !== NULL) {
			$errorHandler->handleException($exception);
		}
		parent::echoExceptionWeb($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function echoExceptionCLI(\Exception $exception) {
		$errorHandler = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('Networkteam\SentryClient\ErrorHandler');
		if ($errorHandler !== NULL) {
			$errorHandler->handleException($exception);
		}
		parent::echoExceptionWeb($exception);
	}

}