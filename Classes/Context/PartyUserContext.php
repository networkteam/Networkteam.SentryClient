<?php

namespace Networkteam\SentryClient\Context;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

use Neos\Flow\Annotations as Flow;
use Neos\Party\Domain\Model\Person;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Security\Context as SecurityContext;

class PartyUserContext implements UserContextServiceInterface
{
    /**
     * @Flow\Inject
     *
     * @var PartyRepository
     */
    protected $partyRepository;

    /**
     * Returns ContextData to be added to the sentry entry.
     *
     * @param SecurityContext $securityContext
     *
     * @return array
     */
    public function getUserContext(SecurityContext $securityContext)
    {
        $account = $securityContext->getAccount();
        if ($account) {
            $party = $this->partyRepository->findOneHavingAccount($account);
            $userContext = [];
            if ($party instanceof Person && $party->getPrimaryElectronicAddress() !== null) {
                $userContext['email'] = (string) $party->getPrimaryElectronicAddress();
            } elseif ($party !== null && ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
                $userContext['email'] = (string) ObjectAccess::getProperty($party, 'emailAddress');
            }

            return $userContext;
        }

        return [];
    }
}
