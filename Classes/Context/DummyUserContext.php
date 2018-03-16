<?php
namespace Networkteam\SentryClient\Context;

use Neos\Flow\Security\Context;

class DummyUserContext implements UserContextServiceInterface
{

    /**
     * Returns ContextData to be added to the sentry entry
     *
     * @param Context $securityContext
     * @return array
     */
    public function getUserContext(Context $securityContext)
    {
        return [];
    }
}
