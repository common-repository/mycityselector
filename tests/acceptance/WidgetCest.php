<?php

use Helper\Acceptance;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\OptionsInterface;
use Step\Acceptance\User;

class WidgetCest extends BaseCest {

	public function _after( User $I ) {
		parent::_after( $I );
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testWidgetTitle( User $i ) {
		$title = uniqid();
		$i->updateWidgetOptions( $title );
		$i->amOnPage( '/' );
		$i->waitForElement( '#mcs-widget > div > a', 3 );
		$i->canSeeElementInDOM( '#mcs-widget > div > a' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );
		$i->waitForElement( '.MuiDialogTitle-root h6' );
		$i->see( $title, '.MuiDialogTitle-root h6' );
	}

	public function testListModeCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions();
		$i->amOnPage( '/' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		foreach ( $this->countries as $country ) {
			$i->dontSee( $country->getTitle() );
			foreach ( $country->getProvinces() as $province ) {
				$i->dontSee( $province->getTitle() );
				foreach ( $province->getCities() as $city ) {
					$i->see( $city->getTitle() );
				}
			}
		}
	}

	public function testListModeProvincesCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_PROVINCES_CITIES );
		$i->amOnPage( '/' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		foreach ( $this->countries as $country ) {
			$i->dontSee( $country->getTitle() );
			foreach ( $country->getProvinces() as $province ) {
				$i->see( $province->getTitle() );
				$i->click( '#mcs-province-' . $province->getId() . ' span' );
				foreach ( $province->getCities() as $city ) {
					$i->see( $city->getTitle() );
				}
			}
		}
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testListModeCountriesProvincesCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_COUNTRIES_PROVINCES_CITIES );
		$i->amOnPage( '/' );
		$i->waitForElementVisible( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		foreach ( $this->countries as $country ) {
			$i->see( $country->getTitle() );
			$i->click( '#mcs-country-' . $country->getId() . ' span' );
			foreach ( $country->getProvinces() as $province ) {
				$i->see( $province->getTitle() );
				$i->click( '#mcs-province-' . $province->getId() . ' span' );
				foreach ( $province->getCities() as $city ) {
					$i->see( $city->getTitle() );
				}
			}
		}
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testListModeCountriesCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_COUNTRIES_CITIES );
		$i->amOnPage( '/' );
		$i->waitForElementVisible( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		$i->waitForElementVisible( '#mcs-dialog' );
		foreach ( $this->countries as $country ) {
			$i->see( $country->getTitle() );
			$i->click( '#mcs-country-' . $country->getId() . ' span' );
			foreach ( $country->getProvinces() as $province ) {
				$i->dontSee( $province->getTitle() );
				foreach ( $province->getCities() as $city ) {
					$i->see( $city->getTitle() );
				}
			}
		}
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testListModeCountries( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_COUNTRIES );
		$i->amOnPage( '/' );
		$i->waitForElementVisible( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		$i->waitForElementVisible( '#mcs-dialog' );
		foreach ( $this->countries as $country ) {
			$i->see( $country->getTitle(), '#mcs-dialog' );
			foreach ( $country->getProvinces() as $province ) {
				$i->dontSee( $province->getTitle(), '#mcs-dialog' );
				foreach ( $province->getCities() as $city ) {
					$i->dontSee( $city->getTitle(), '#mcs-dialog' );
				}
			}
		}
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testDisablePopup( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_COUNTRIES );
		$i->amOnPage( '/' );
		$i->waitForElement( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-yes' );
		$i->reloadPage();
		$i->assertEquals( DataInterface::LOCATION_TYPE_CITY, $i->grabCookie( DataInterface::COOKIE_LOCATION_TYPE ) );
		$i->assertEquals( $this->defaultCity->getId(), $i->grabCookie( DataInterface::COOKIE_LOCATION_ID ) );
		$i->assertEquals( '1', $i->grabCookie( DataInterface::COOKIE_DISABLE_POPUP ) );
		$i->waitForElementNotVisible( '#mcs-popup p' );
		$i->dontSee( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testSubdomainSeoModeListModeCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_SUBDOMAIN );
		$i->updateWidgetOptions();
		$i->amOnPage( '/' );
		$i->waitForElement( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );
		$i->waitForElementVisible( '#mcs-dialog' );

		$i->click( '#mcs-city-' . $this->notDefaultCity->getId() . ' span' );
		$i->assertEquals( $this->notDefaultCity->getSubDomain() . '.' . Acceptance::BASE_DOMAIN, $i->getHost() );

		$i->click( '#mcs-link' );
		$i->waitForElementVisible( '#mcs-dialog' );
		$i->click( '#mcs-city-' . $this->defaultCity->getId() . ' span' );
		$i->assertEquals( Acceptance::BASE_DOMAIN, $i->getHost() );
	}

	/**
	 * @param User $i
	 *
	 * @throws Exception
	 */
	public function testSubFolderSeoModeListModeCities( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_SUBFOLDER );
		$i->updateWidgetOptions();
		$i->amOnPage( '/' );
		$i->waitForElement( '#mcs-popup p' );
		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );
		$i->waitForElementVisible( '#mcs-dialog' );

		$i->click( '#mcs-city-' . $this->notDefaultCity->getId() . ' span' );
		$i->seeCurrentUrlEquals( '/' . $this->notDefaultCity->getSubDomain() . '/' );

		$i->click( '#mcs-link' );
		$i->waitForElementVisible( '#mcs-dialog' );
		$i->click( '#mcs-city-' . $this->defaultCity->getId() . ' span' );
		$i->seeCurrentUrlEquals( '/' );
	}
}
