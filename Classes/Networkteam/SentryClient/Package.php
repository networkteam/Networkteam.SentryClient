<?php
namespace Networkteam\SentryClient;

/***************************************************************
 *  (c) 2015 networkteam GmbH - all rights reserved
 ***************************************************************/

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The SentryClient Package
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 *
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		require_once(FLOW_PATH_PACKAGES . '/Libraries/raven/raven/lib/Raven/Autoloader.php');
		\Raven_Autoloader::register();

		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function($step, $runlevel) use($bootstrap) {
			if ($step->getIdentifier() === 'typo3.flow:objectmanagement:runtime') {
				// This triggers the initializeObject method
				$bootstrap->getObjectManager()->get('Networkteam\SentryClient\ErrorHandler');
			}
		});
	}
}