<?php


namespace Mcs\Interfaces;

interface ModelInterface {

	public function getId(): int;

	/**
	 * @param int $id
	 *
	 * @return ModelInterface|static
	 */
	public static function findById( int $id ): ?ModelInterface;

	/**
	 * @param $property
	 * @param $value
	 *
	 * @return ModelInterface
	 */
	public static function findFirstByPropertyValue( $property, $value ): ModelInterface;

	/**
	 * @param $property
	 * @param $value
	 *
	 * @return ModelInterface[]
	 */
	public static function findByPropertyValue( $property, $value ): array;

	/**
	 * @param int|null $limit
	 *
	 * @return ModelInterface[]
	 */
	public static function all(int $limit = null): array;

	public static function total(): int;

	public static function create( $data = [] ): ModelInterface;

	public function update( $data = [] ): ModelInterface;

	public function delete(): bool;

	public static function getTableName(): string;

	public function getProperties(): array;

	public function getType(): int;
}
