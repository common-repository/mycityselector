<?php

use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\ModelInterface;
use Mcs\Interfaces\ProvincesInterface;
use Mcs\WpModels\Cities;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Options;
use Mcs\WpModels\Provinces;
use PHPUnit\Framework\TestCase;

class testMcsData extends TestCase {

	/**
	 * @var CountriesInterface
	 */
	protected $country;

	/**
	 * @var ProvincesInterface
	 */
	protected $province;

	/**
	 * @var CitiesInterface
	 */
	protected $city;

	protected function setUp() {
		$this->country                  = Countries::create( [
			'title'     => 'Test Country',
			'subdomain' => 'test-country',
			'published' => 1,
			'code'      => 'ru',
			'domain'    => 'ru'
		] );
		$this->province                 = Provinces::create( [
			'title'      => 'Test Province',
			'country_id' => $this->country->id,
			'subdomain'  => 'test-province',
			'published'  => 1
		] );
		$this->city                     = Cities::create( [
			'title'       => 'Test City',
			'country_id'  => $this->country->id,
			'province_id' => $this->province->id,
			'subdomain'   => 'test-city',
			'published'   => 1
		] );
		$this->country->default_city_id = $this->city->id;
		$this->country->update( get_object_vars( $this->country ) );

	}

	/**
	 * @throws Exception
	 */
	protected function tearDown() {
		$this->country->default_city_id = null;
		$this->country->update( get_object_vars( $this->country ) );

		$this->city->delete();
		$this->province->delete();
		$this->country->delete();
	}

	public function testGetDefaultLocations() {
		$data = new class( $this->city ) extends Options {
			protected Cities $defaultCity;

			public function __construct( $defaultCityId ) {
				$this->defaultCity = $defaultCityId;
			}

			public function getDefaultLocation(): ModelInterface {
				return $this->defaultCity;
			}
		};

		/** @var Cities $location */
		$location = $data->getDefaultLocation();
		$this->assertEquals( $this->city->id, $data->getDefaultLocation()->id );
		$this->assertEquals( $this->province->id, $location->getProvince()->getId() );
		$this->assertEquals( $this->country->id, $location->getCountry()->getId() );
	}

//	public function testDetectCurrentLocationFromCookie() {
//
//	}
}
