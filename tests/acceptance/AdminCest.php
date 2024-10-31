<?php


use Step\Acceptance\Admin;
use Step\Acceptance\User;

class AdminCest extends BaseCest {
	public function _after( User $I ) {
		parent::_after( $I );
	}

	/**
	 * @param Admin $I
	 *
	 * @throws Exception
	 */
	public function adminPageTest( Admin $I ) {
		$I->goToMcsPage();
	}
}
