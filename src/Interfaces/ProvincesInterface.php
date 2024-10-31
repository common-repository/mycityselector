<?php


namespace Mcs\Interfaces;

use Exception;

interface ProvincesInterface {
	/**
	 * @param int $countryId
	 * @param string $name
	 *
	 * @return static
	 * @throws Exception
	 */
	public static function findByName( $countryId, $name );

	public function getId(): int;

	/**
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * @return CitiesInterface[]
	 */
	public function getCities(): array;

	public function isPublished(): bool;

	public function getCountry() : CountriesInterface;
}
