<?php
namespace Networkteam\SentryClient\Context;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Utility\Exception\PropertyNotAccessibleException;
use Neos\Utility\ObjectAccess;

class PartyUserContext implements UserContextServiceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns ContextData to be added to the sentry entry
     *
     * @param Context $securityContext
     * @return array
     * @throws PropertyNotAccessibleException
     */
    public function getUserContext(Context $securityContext)
    {
        $userContext = [];
        $account = $securityContext->getAccount();
        if ($account && class_exists('Neos\Party\Domain\Repository\PartyRepository')) {
            $partyRepository = $this->objectManager->get('Neos\Party\Domain\Repository\PartyRepository');
            $party = $partyRepository->findOneHavingAccount($account);
            if ($party instanceof \Neos\Party\Domain\Model\Person && $party->getPrimaryElectronicAddress() !== null) {
                $userContext['email'] = (string)$party->getPrimaryElectronicAddress();
            } elseif ($party !== null && ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
                $userContext['email'] = (string)ObjectAccess::getProperty($party, 'emailAddress');
            }
        }
        
        return $userContext;
    }
}
