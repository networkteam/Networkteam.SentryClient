<?php
namespace Networkteam\SentryClient\Context;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;
use Neos\Party\Domain\Model\Person;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Utility\ObjectAccess;

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
     * @param Context $securityContext
     * @return array
     */
    public function getUserContext(Context $securityContext)
    {
        $account = $securityContext->getAccount();
        if ($account) {
            $party = $this->partyRepository->findOneHavingAccount($account);
            $userContext = [];
            if ($party instanceof Person && $party->getPrimaryElectronicAddress() !== null) {
                $userContext['email'] = (string)$party->getPrimaryElectronicAddress();
            } elseif ($party !== null && ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
                $userContext['email'] = (string)ObjectAccess::getProperty($party, 'emailAddress');
            }

            return $userContext;
        }

        return [];
    }
}
