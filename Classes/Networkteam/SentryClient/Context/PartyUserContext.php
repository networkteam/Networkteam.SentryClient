<?php
namespace Networkteam\SentryClient\Context;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

use TYPO3\Party\Domain\Model\Person;

class PartyUserContext implements UserContextServiceInterface {

	/**
	 * Returns ContextData to be added to the sentry entry
	 *
	 * @param \TYPO3\Flow\Security\Context $securityContext
	 * @return array
	 */
	public function getUserContext(\TYPO3\Flow\Security\Context $securityContext) {
		$party = $securityContext->getParty();
		$userContext = [];
		if ($party instanceof Person && $party->getPrimaryElectronicAddress() !== NULL) {
			$userContext['email'] = (string)$party->getPrimaryElectronicAddress();
		} elseif ($party !== NULL && \TYPO3\Flow\Reflection\ObjectAccess::isPropertyGettable($party, 'emailAddress')) {
			$userContext['email'] = (string)\TYPO3\Flow\Reflection\ObjectAccess::getProperty($party, 'emailAddress');
		}

		return $userContext;
	}
}