<?php


use Mcs\Interfaces\ModelInterface;
use Mcs\WpModels\Cities;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Provinces;
use Phinx\Seed\AbstractSeed;

class CitiesSeeder extends AbstractSeed {

	public function getDependencies() {
		return [
			'TruncateAll'
		];
	}

	/**
	 * @var Countries[]
	 */
	protected $countries = [];

	/**
	 * @var Provinces[]
	 */
	protected $provinces = [];

	/**
	 * @var Cities[]
	 */
	protected $cities = [];

	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * https://book.cakephp.org/phinx/0/en/seeding.html
	 * @throws Exception
	 */
	public function run() {
		$handle = fopen( __DIR__ . '/GeoLite2-City-Locations-en.csv', 'r' );
		$i      = 1000;
		if ( $handle ) {
			fgets( $handle, 4096 );
			while ( ( $buffer = fgets( $handle, 4096 ) ) !== false ) {
				$cityData = explode( ',', trim( $buffer ) );
				if ( empty( $cityData[10] ) ) {
					continue;
				}
				$countryName = trim( $cityData[5], '" ' );
				$country     = $this->findOrCreateCountry( $countryName, $cityData[4] );

				$provinceName = trim( $cityData[7], '" ' );
				$province     = $this->findOrCreateProvince( $country->id, $provinceName );

				$cityName = trim( $cityData[10], '" ' );
				$this->findOrCreateCity( $country->id, $province->id, $cityName );

				$i --;
				if ( $i == 0 ) {
					break;
				}
			}
			fclose( $handle );
		}
	}

	/**
	 * @param $name
	 * @param $code
	 *
	 * @return Countries
	 * @throws Exception
	 */
	protected function findOrCreateCountry( $name, $code ) {
		if ( isset( $this->countries[ $name ] ) ) {
			return $this->countries[ $name ];
		}

		try {
			//$countryName = CountryNames::findByPropertyValue( 'name', $name );
			$country = Countries::findFirstByPropertyValue( 'title', $name );
		} catch ( Exception $exception ) {
			$country = Countries::create( [
				'title'     => $name,
				'subdomain' => $code,
				'code'      => $code,
			] );
			/*			CountryNames::create( [
							'country_id' => $country->id,
							'lang_code'  => 'en_US',
							'name'       => $name
						] );*/
		}
		$this->countries[ $name ] = $country;

		return $country;
	}

	/**
	 * @param $countryId
	 * @param $name
	 *
	 * @return Provinces
	 * @throws Exception
	 */
	protected function findOrCreateProvince( $countryId, $name ) {
		if ( isset( $this->provinces[ $countryId ][ $name ] ) ) {
			return $this->provinces[ $countryId ][ $name ];
		}

		try {
			//$provinceName = ProvinceName::findByName( $countryId, $name );
			$province = Provinces::findByName( $countryId, $name );
		} catch ( Exception $exception ) {
			$province = Provinces::create( [
				'title'      => $name,
				'country_id' => $countryId,
				'subdomain'  => sanitize_title( $name ),
			] );
			/*			ProvinceName::create( [
							'province_id' => $province->id,
							'lang_code'   => 'en_US',
							'name'        => $name
						] );*/
		}
		$this->provinces[ $countryId ][ $name ] = $province;

		return $province;
	}

	/**
	 * @param $countryId
	 * @param $provinceId
	 * @param $name
	 *
	 * @return ModelInterface|Provinces
	 * @throws Exception
	 */
	protected function findOrCreateCity( $countryId, $provinceId, $name ) {
		if ( isset( $this->cities[ $countryId ][ $provinceId ][ $name ] ) ) {
			return $this->cities[ $countryId ][ $provinceId ][ $name ];
		}

		try {
//			$cityName = CityNames::findByName( $countryId, $provinceId, $name );
			$city = Cities::findByTitle( $countryId, $provinceId, $name );
		} catch ( Exception $exception ) {
			$city = Cities::create( [
				'title'       => $name,
				'country_id'  => $countryId,
				'province_id' => $provinceId,
				'subdomain'   => sanitize_title( $name ),
			] );
			/*			CityNames::create( [
							'city_id'   => $city->id,
							'lang_code' => 'en_US',
							'name'      => $name
						] );*/
		}

		if ( ! isset( $this->cities[ $countryId ] ) ) {
			$this->cities[ $countryId ] = [];
		}

		if ( ! isset( $this->cities[ $countryId ][ $provinceId ] ) ) {
			$this->cities[ $countryId ][ $provinceId ] = [];
		}

		$this->cities[ $countryId ][ $provinceId ][ $name ] = $city;

		return $city;
	}

}
