<?php


namespace Mcs\Interfaces;

use Exception;

interface CitiesInterface {
	/**
	 * @param int $countryId
	 * @param int $provinceId
	 * @param string $title
	 *
	 * @return static
	 * @throws Exception
	 */
	public static function findByTitle( int $countryId, int $provinceId, string $title ): ?CitiesInterface;

	public function getId(): int;

	public function getTitle(): string;

	public function getProvince(): ProvincesInterface;

	public function getCountry(): CountriesInterface;

	public function getSubDomain(): ?string;

	public function isPublished(): bool;
}
