<?php

namespace Networkteam\SentryClient\Context;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

use Neos\Flow\Security\Context as SecurityContext;

class DummyUserContext implements UserContextServiceInterface
{
    /**
     * Returns ContextData to be added to the sentry entry.
     *
     * @param SecurityContext $securityContext
     *
     * @return array
     */
    public function getUserContext(SecurityContext $securityContext)
    {
        return array();
    }
}
