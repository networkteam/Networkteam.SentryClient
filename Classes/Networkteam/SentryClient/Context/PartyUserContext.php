<?php
namespace Networkteam\SentryClient\Context;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

use Neos\Flow\Annotations as Flow;
use Neos\Party\Domain\Model\Person;
use Neos\Party\Domain\Repository\PartyRepository;

class PartyUserContext implements UserContextServiceInterface
{

    /**
     * @Flow\Inject
     * @var PartyRepository
     */
    protected $partyRepository;

    /**
     * Returns ContextData to be added to the sentry entry
     *
     * @param \Neos\Flow\Security\Context $securityContext
     * @return array
     */
    public function getUserContext(\Neos\Flow\Security\Context $securityContext)
    {
        $account = $securityContext->getAccount();
        if ($account) {
            $party = $this->partyRepository->findOneHavingAccount($account);
            $userContext = [];
            if ($party instanceof Person && $party->getPrimaryElectronicAddress() !== NULL) {
                $userContext['email'] = (string)$party->getPrimaryElectronicAddress();
            } elseif ($party !== NULL && \Neos\Utility\ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
                $userContext['email'] = (string)\Neos\Utility\ObjectAccess::getProperty($party, 'emailAddress');
            }

            return $userContext;
        }

        return [];
	}
}