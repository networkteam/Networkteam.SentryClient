<?php
namespace Networkteam\SentryClient;

use TYPO3\Flow\Package\Package as BasePackage;

class Package extends BasePackage {

	/**
	 * {@inheritdoc}
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		require_once(FLOW_PATH_PACKAGES . '/Libraries/sentry/sentry/lib/Raven/Autoloader.php');
		\Raven_Autoloader::register();

		$bootstrap->getSignalSlotDispatcher()->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function($step, $runlevel) use($bootstrap) {
			if ($step->getIdentifier() === 'typo3.flow:objectmanagement:runtime') {
				// This triggers the initializeObject method
				$bootstrap->getObjectManager()->get('Networkteam\SentryClient\ErrorHandler');
			}
		});
	}
}