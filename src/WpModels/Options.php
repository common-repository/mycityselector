<?php


namespace Mcs\WpModels;

use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\ModelInterface;
use Mcs\Interfaces\OptionsInterface;

class Options implements OptionsInterface {
	/**
	 * @var Cities|Provinces|Countries
	 */
	protected $defaultLocation;

	public function getBaseDomain(): string {
		$siteUrl = site_url();

		//		return (string) get_option( 'mcs_base_domain' );
		return parse_url( $siteUrl, PHP_URL_HOST );
	}

//	public function setBaseDomain( string $domain ): bool {
//		return update_option( 'mcs_base_domain', $domain );
//	}

	public function getDefaultLocation(): ?ModelInterface {
		if ( empty( $this->defaultLocation ) ) {
			switch ( $this->getDefaultLocationType() ) {
				case DataInterface::LOCATION_TYPE_CITY:
					$city = Cities::findById( $this->getDefaultLocationId() );
					if ( $city ) {
						$this->defaultLocation = $city;
					}
					break;
				case DataInterface::LOCATION_TYPE_PROVINCE:
					$province = Provinces::findById( $this->getDefaultLocationId() );
					if ( $province ) {
						$this->defaultLocation = $province;
					}
					break;
				case DataInterface::LOCATION_TYPE_COUNTRY:
					$country = Countries::findById( $this->getDefaultLocationId() );
					if ( $country ) {
						$this->defaultLocation = $country;
					}
					break;
			}
		}

		return $this->defaultLocation;
	}

	public function updateDefaultLocationId( int $defaultLocationId = null ): bool {
		return update_option( 'mcs_default_location_id', $defaultLocationId );
	}

	public function getSeoMode(): int {
		return (int) get_option( 'mcs_seo_mode' );
	}

	public function setSeoMode( int $seoMode = self::SEO_MODE_COOKIE ): bool {
		return update_option( 'mcs_seo_mode', $seoMode );
	}

	public function getCountryChooseEnabled(): bool {
		return (bool) get_option( 'mcs_country_choose_enabled' );
	}

	public function setCountryChooseEnabled( bool $countryChooseEnabled = false ): bool {
		return update_option( 'mcs_country_choose_enabled', $countryChooseEnabled );
	}

	public function getProvinceChooseEnabled(): bool {
		return (bool) get_option( 'mcs_province_choose_enabled' );
	}

	public function setProvinceChooseEnabled( bool $provinceChooseEnabled ): bool {
		return update_option( 'mcs_province_choose_enabled', $provinceChooseEnabled );
	}

	public function getAskMode(): int {
		return (int) get_option( 'mcs_ask_mode' );
	}

	public function setAskMode( int $askMode = self::ASK_MODE_DIALOG ): bool {
		return update_option( 'mcs_ask_mode', $askMode );
	}

	public function getRedirectNextVisits(): bool {
		return (bool) get_option( 'mcs_redirect_next_visits' );
	}

	public function setRedirectNextVisits( bool $redirectNextVisits = false ): bool {
		return update_option( 'mcs_redirect_next_visits', $redirectNextVisits );
	}

	public function getLogEnabled(): bool {
		return (bool) get_option( 'mcs_log_enabled' );
	}

	public function setLogEnabled( bool $logEnabled = false ): bool {
		return update_option( 'mcs_log_enabled', $logEnabled );
	}

	public function getDebugEnabled(): bool {
		return (bool) get_option( 'mcs_debug_enabled' );
	}

	public function setDebugEnabled( bool $debugEnabled = false ): bool {
		return update_option( 'mcs_debug_enabled', $debugEnabled );
	}

	public function toArray(): array {
		$defaultLocation = $this->getDefaultLocation();

		return [
			'id'                      => 0,
			//			'base_domain'             => $this->getBaseDomain(),
			'default_location_id'     => $defaultLocation ? $defaultLocation->getId() : null,
			'default_location_type'   => $defaultLocation ? $defaultLocation->getType() : null,
			'seo_mode'                => $this->getSeoMode(),
			'country_choose_enabled'  => $this->getCountryChooseEnabled(),
			'province_choose_enabled' => $this->getProvinceChooseEnabled(),
			'ask_mode'                => $this->getAskMode(),
			'redirect_next_visits'    => $this->getRedirectNextVisits(),
			'log_enabled'             => $this->getLogEnabled(),
			'debug_enabled'           => $this->getDebugEnabled()
		];
	}

	public static function getInstance(): OptionsInterface {
		static $options;
		if ( ! $options ) {
			$options = new self();
		}

		return $options;
	}

	public function updateDefaultLocationType( int $locationType ) {
		return update_option( 'mcs_default_location_type', $locationType );
	}

	public function getDefaultLocationId() {
		return get_option( 'mcs_default_location_id' );
	}

	public function getDefaultLocationType() {
		return get_option( 'mcs_default_location_type' );
	}

	public function setDefaultLocation( $location ): void {
		$this->defaultLocation = $location;
	}
}
