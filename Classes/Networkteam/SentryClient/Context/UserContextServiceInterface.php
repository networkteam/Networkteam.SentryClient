<?php
namespace Networkteam\SentryClient\Context;

/***************************************************************
 *  (c) 2016 networkteam GmbH - all rights reserved
 ***************************************************************/

interface UserContextServiceInterface {

	/**
	 * Returns ContextData to be added to the sentry entry
	 *
	 * @param \TYPO3\Flow\Security\Context $securityContext
	 * @return array
	 */
	public function getUserContext(\TYPO3\Flow\Security\Context $securityContext);
}