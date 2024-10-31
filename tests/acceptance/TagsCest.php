<?php

use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\OptionsInterface;
use Step\Acceptance\User;

class TagsCest extends BaseCest {

	public function testMcsTagDefaultValue( User $i ) {
		$i->updateWidgetOptions( 'Select your city', DataInterface::LIST_MODE_CITIES );
		$tag                = sprintf( '{mcs-%d}', $this->field->getId() );
		$post               = $i->getPost();
		$post->post_content = $post->post_content . $tag;
		$i->updatePost( $post );
		$i->amOnPage( '/' );
		$i->canSeeInPageSource( $this->fieldValueDefault->getValue() );
	}

	public function testMcsTagCityValueCookieMode( User $i ) {
		$i->getOptions()->setSeoMode( OptionsInterface::SEO_MODE_COOKIE );
		$i->updateWidgetOptions( null, DataInterface::LIST_MODE_CITIES );
		$tag                = sprintf( '{mcs-%d}', $this->field->getId() );
		$post               = $i->getPost();
		$post->post_content = $post->post_content . $tag;
		$i->updatePost( $post );
		$i->amOnPage( '/' );

		$i->see( 'Is ' . $this->defaultCity->getTitle() . ' your city', '#mcs-popup p' );
		$i->click( '#mcs-popup-no' );

		$i->see( $this->notDefaultCity->getTitle(), '#mcs-city-' . $this->notDefaultCity->getId() . ' span' );
		$i->click( '#mcs-city-' . $this->notDefaultCity->getId() . ' span' );

		$i->seeCookie( DataInterface::COOKIE_LOCATION_TYPE );
		$locationTypeCookie = $i->grabCookie( DataInterface::COOKIE_LOCATION_TYPE );
		$i->assertEquals( DataInterface::LOCATION_TYPE_CITY, $locationTypeCookie );


		$i->seeCookie( DataInterface::COOKIE_LOCATION_ID );
		$locationId = $i->grabCookie( DataInterface::COOKIE_LOCATION_ID );
		$i->assertEquals( $this->notDefaultCity->getId(), $locationId );

		$i->canSeeInPageSource( $this->fieldValue->getValue() );
		$i->dontSeeElement( '#mcs-popup' );
	}

}
