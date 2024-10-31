<?php

namespace Mcs\WpModels;

use Exception;
use Mcs\Interfaces\CitiesInterface;
use Mcs\Interfaces\CookiesAdapterInterface;
use Mcs\Interfaces\CountriesInterface;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\FieldsInterface;
use Mcs\Interfaces\ModelInterface;
use Mcs\Interfaces\OptionsInterface;
use Mcs\Interfaces\ProvincesInterface;
use Mcs\WpAdapters\CookiesAdapter;

class Data implements DataInterface {

	/**
	 * @var OptionsInterface
	 */
	protected $options;

	/**
	 * @var CookiesAdapterInterface
	 */
	protected $cookies;

	/**
	 * Current detected location (Country, Province or City)
	 * @var null|CitiesInterface|ProvincesInterface|CountriesInterface
	 */
	protected $currentLocation = null;

	protected $currentLocationType = null;

	/**
	 * @var CitiesInterface
	 */
	protected $currentCity = null;

	/**
	 * @var ProvincesInterface
	 */
	protected $currentProvince = null;

	/**
	 * @var CountriesInterface
	 */
	protected $currentCountry = null;

	/**
	 * @var FieldsInterface[]
	 */
	protected $fields = [];

	public static function getInstance(): DataInterface {
		static $instance;
		if ( ! $instance ) {
			$instance = new Data();
		}

		return $instance;
	}

	/**
	 * Data constructor.
	 *
	 * @param OptionsInterface|null $options
	 */
	public function __construct( OptionsInterface $options = null, CookiesAdapterInterface $cookies = null ) {
		$this->options = $options ?: Options::getInstance();
		$this->cookies = $cookies ?: new CookiesAdapter();
	}

	/**
	 * @param string $body
	 *
	 * @return string
	 * @throws Exception
	 */
	public function replaceTags( string $body ): string {
		if ( preg_match_all( '/{mcs-(\d+)}/iu', $body, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$value   = '';
				$fieldId = (int) $match[1];
				if ( empty( $this->fields[ $fieldId ] ) ) {
					$this->fields[ $fieldId ] = Fields::findById( $fieldId );
				}
				if ( $this->fields[ $fieldId ] instanceof ModelInterface ) {
					$value = $this->fields[ $fieldId ]
						->getFieldValueForLocation( $this->getCurrentLocation() );
				}
				$body = str_replace( $match[0], $value, $body );
			}
		}

		return $body;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getWidgetDataJson(): string {
		$countries = [];
		foreach ( Countries::findByPropertyValue( 'published', 1 ) as $country ) {
			$countries[ $country->id ] = $country;
		}
		$provinces = [];
		foreach ( Provinces::findByPropertyValue( 'published', 1 ) as $province ) {
			$provinces[ $province->id ] = $province;
		}
		$cities = [];
		foreach ( Cities::findByPropertyValue( 'published', 1 ) as $city ) {
			$cities[ $city->id ] = $city;
		}

		return json_encode( [
			'countries'             => $countries,
			'provinces'             => $provinces,
			'cities'                => $cities,
			'current_location_id'   => $this->getCurrentLocation() ? $this->getCurrentLocation()->getId() : null,
			'current_location_type' => $this->getCurrentLocationType()
		] );
	}

	/**
	 * @throws Exception
	 */
	public function updateCurrentLocations() {
		if ( ! $this->currentLocation ) {
			$this->getCurrentLocation();
		}
		switch ( get_class( $this->currentLocation ) ) {
			case Countries::class;
				$this->currentCountry  = $this->currentLocation;
				$this->currentCity     = $this->currentCountry->getDefaultCity();
				$this->currentProvince = $this->currentCity->getProvince();
				break;
			case Provinces::class:
				$this->currentProvince = $this->currentLocation;
				$this->currentCountry  = $this->currentProvince->getCountry();
				$this->currentCity     = $this->currentCountry->getDefaultCity();
				break;
			case Cities::class:
				$this->currentCity     = $this->currentLocation;
				$this->currentProvince = $this->currentCity->getProvince();
				$this->currentCountry  = $this->currentCity->getCountry();
				break;
		}
	}

	/**
	 * @return CountriesInterface|ProvincesInterface|CitiesInterface|ModelInterface
	 * @throws Exception
	 */
	public function getCurrentLocation(): ?ModelInterface {
		if ( empty( $this->currentLocation ) ) {
			switch ( $this->options->getSeoMode() ) {
				case OptionsInterface::SEO_MODE_COOKIE:
					$this->detectCurrentLocationFromCookie();
					break;
				case OptionsInterface::SEO_MODE_SUBDOMAIN:
					$this->detectCurrentLocationFromSubdomain();
					break;
				case OptionsInterface::SEO_MODE_SUBFOLDER:
					$this->detectCurrentLocationFromSubFolder();
					break;
				default:
					$this->currentLocation     = $this->options->getDefaultLocation();
					$this->currentLocationType = self::LOCATION_TYPE_CITY;
			}
		}

		return $this->currentLocation;
	}

	/**
	 * Mutates currentLocation and currentLocationType
	 */
	protected function detectCurrentLocationFromCookie() {
//		$locationType = key_exists( 'mcs_location_type', $_COOKIE ) ? $_COOKIE['mcs_location_type'] : null;
		$locationType = $this->cookies->getInt( 'mcs_location_type' );
//		$locationId   = $_COOKIE['mcs_location_id'] ?? null;
		$locationId = $this->cookies->getInt( 'mcs_location_id' );
		switch ( $locationType ) {
			case self::LOCATION_TYPE_CITY:
				$model = Cities::findById( $locationId );
				if ( $model instanceof CitiesInterface ) {
					$this->currentLocation     = $model;
					$this->currentLocationType = self::LOCATION_TYPE_CITY;
				}
				break;
			case self::LOCATION_TYPE_PROVINCE:
				$model = Provinces::findById( $locationId );
				if ( $model instanceof ProvincesInterface ) {
					$this->currentLocation     = $model;
					$this->currentLocationType = self::LOCATION_TYPE_PROVINCE;
				}
				break;
			case self::LOCATION_TYPE_COUNTRY:
				$model = Countries::findById( $locationId );
				if ( $model instanceof CountriesInterface ) {
					$this->currentLocation     = $model;
					$this->currentLocationType = self::LOCATION_TYPE_COUNTRY;
				}
				break;
		}
		if ( empty( $this->currentLocation ) ) {
			$this->currentLocation     = $this->options->getDefaultLocation();
			$this->currentLocationType = $this->options->getDefaultLocationType();
		}
	}


	protected function detectCurrentLocationFromSubdomain() {
		$host      = $_SERVER['HTTP_HOST'];
		$subdomain = trim( str_ireplace( $this->options->getBaseDomain(), '', $host ), '.' );

		try {
			if ( ! empty( $subdomain ) && $this->setLocationBySubdomain( $subdomain ) ) {
				return;
			}
		} catch ( Exception $exception ) {

		}

		$this->currentLocation     = $this->options->getDefaultLocation();
		$this->currentLocationType = $this->options->getDefaultLocationType();
	}

	protected function detectCurrentLocationFromSubFolder(): void {
		$uri = ltrim( $_SERVER['REQUEST_URI'], '/' );
		try {
			if (
				preg_match( '#[^/?]+#', $uri, $matches )
				&& ! empty( $matches[0] )
				&& $this->setLocationBySubdomain( $matches[0] ) ) {
				$_SERVER['REQUEST_URI'] = str_replace( "/{$this->currentLocation->getSubDomain()}", '', $_SERVER['REQUEST_URI'] );

				return;
			}
		} catch ( Exception $exception ) {

		}

		$this->currentLocation     = $this->options->getDefaultLocation();
		$this->currentLocationType = $this->options->getDefaultLocationType();
	}

	/**
	 * @param string $subdomain
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function setLocationBySubdomain( string $subdomain ) {
		$city = Cities::findFirstByPropertyValue( 'subdomain', $subdomain );
		if ( $city && $city->isPublished() && $city->getProvince()->isPublished() && $city->getCountry()->isPublished() ) {
			$this->currentLocation     = $city;
			$this->currentLocationType = self::LOCATION_TYPE_CITY;

			return true;
		}
		$province = Provinces::findFirstByPropertyValue( 'subdomain', $subdomain );
		if ( $province && $province->isPublished() && $province->getCountry()->isPublished() ) {
			$this->currentLocation     = $province;
			$this->currentLocationType = self::LOCATION_TYPE_PROVINCE;

			return true;
		}

		$country = Countries::findFirstByPropertyValue( 'subdomain', $subdomain );
		if ( $country && $country->isPublished() ) {
			$this->currentLocation     = $country;
			$this->currentLocationType = self::LOCATION_TYPE_COUNTRY;

			return true;
		}

		return false;
	}


	/**
	 * @return CitiesInterface
	 * @throws Exception
	 */
	public function getCurrentCity(): CitiesInterface {
		if ( empty( $this->currentCity ) ) {
			$this->updateCurrentLocations();
		}

		return $this->currentCity;
	}

	/**
	 * @return ProvincesInterface
	 * @throws Exception
	 */
	public function getCurrentProvince(): ProvincesInterface {
		if ( empty( $this->currentProvince ) ) {
			$this->updateCurrentLocations();
		}

		return $this->currentProvince;
	}

	/**
	 * @return CountriesInterface
	 * @throws Exception
	 */
	public function getCurrentCountry(): CountriesInterface {
		if ( empty( $this->currentCountry ) ) {
			$this->updateCurrentLocations();
		}

		return $this->currentCountry;
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	public function getCurrentLocationType(): int {
		if ( empty( $this->currentLocationType ) ) {
			$this->getCurrentLocation();
		}

		return $this->currentLocationType ?? 0;
	}
}
