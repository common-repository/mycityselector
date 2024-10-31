<?php


namespace Mcs\Interfaces;

use Mcs\WpModels\Cities;
use Mcs\WpModels\Provinces;

interface CountriesInterface {
	public function getId(): int;

	/**
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * @return int
	 */
	public function countProvinces();

	/**
	 * @return int
	 */
	public function countCities();

	/**
	 * @return Provinces[]
	 */
	public function getProvinces();

	/**
	 * @return Cities[]
	 */
	public function getCities();

	public function getDefaultCity(): CitiesInterface;

	public function isPublished(): bool;
}
