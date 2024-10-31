<?php


namespace Mcs\Interfaces;


interface DataInterface {
	const LIST_MODE_CITIES = 1;
	const LIST_MODE_PROVINCES_CITIES = 2;
	const LIST_MODE_COUNTRIES_PROVINCES_CITIES = 3;
	const LIST_MODE_COUNTRIES_CITIES = 4;
	const LIST_MODE_COUNTRIES = 5;

	const LOCATION_TYPE_NONE = 0;
	const LOCATION_TYPE_CITY = 1;
	const LOCATION_TYPE_PROVINCE = 2;
	const LOCATION_TYPE_COUNTRY = 3;

	const COOKIE_LOCATION_TYPE = "mcs_location_type";
	const COOKIE_LOCATION_ID = "mcs_location_id";
	const COOKIE_DISABLE_POPUP = "mcs_disable_popup";

	public function __construct( OptionsInterface $options );

	public static function getInstance(): DataInterface;

	/**
	 * Replace tags on page
	 *
	 * @param string $body
	 *
	 * @return string
	 */
	public function replaceTags( string $body ): string;

	/**
	 * Countries, Provinces and Cities data for widget
	 * @return string
	 */
	public function getWidgetDataJson(): string;

	/**
	 * @return null|CitiesInterface|ProvincesInterface|CountriesInterface
	 */
	public function getCurrentLocation(): ?ModelInterface;

	public function getCurrentLocationType(): int;

	public function getCurrentCity(): CitiesInterface;

	public function getCurrentProvince(): ProvincesInterface;

	public function getCurrentCountry(): CountriesInterface;
}
