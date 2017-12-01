<?php

namespace Networkteam\SentryClient;

use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;

class Package extends BasePackage
{
    /**
     * {@inheritdoc}
     */
    public function boot(Bootstrap $bootstrap)
    {
        require_once FLOW_PATH_PACKAGES.'/Libraries/sentry/sentry/lib/Raven/Autoloader.php';
        \Raven_Autoloader::register();

        $bootstrap->getSignalSlotDispatcher()->connect(Sequence::class, 'afterInvokeStep', function ($step, $runlevel) use ($bootstrap) {
            if ($step->getIdentifier() === 'neos.flow:objectmanagement:runtime') {
                // This triggers the initializeObject method
                $bootstrap->getObjectManager()->get(ErrorHandler::class);
            }
        });
    }
}
