<?php
declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class FieldsTable extends AbstractMigration {
	public function change() {
		$this->table( MCS_PREFIX . 'fields' )
		     ->addColumn( 'name', 'string', [ 'length' => 64 ] )
		     ->addColumn( 'published', 'boolean', [ 'default' => false ] )
		     ->create();

		$this->table( MCS_PREFIX . 'field_values' )
		     ->addColumn( 'field_id', 'integer' )
		     ->addForeignKey( 'field_id', MCS_PREFIX . 'fields', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'value', 'text', [ 'null' => true ] )
		     ->addColumn( 'default', 'boolean', [ 'default' => false ] )
		     ->addColumn( 'is_ignore', 'boolean', [ 'default' => false ] )
		     ->create();

		$this->table( MCS_PREFIX . 'country_field_values' )
		     ->addColumn( 'field_id', 'integer' )
		     ->addForeignKey( 'field_id', MCS_PREFIX . 'fields', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'field_value_id', 'integer' )
		     ->addForeignKey( 'field_value_id', MCS_PREFIX . 'field_values', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'country_id', 'integer' )
		     ->addForeignKey( 'country_id', MCS_PREFIX . 'countries', 'id', [ 'delete' => 'cascade' ] )
		     ->addIndex( [ 'country_id', 'field_id' ] )
		     ->create();

		$this->table( MCS_PREFIX . 'province_field_values' )
		     ->addColumn( 'field_id', 'integer' )
		     ->addForeignKey( 'field_id', MCS_PREFIX . 'fields', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'field_value_id', 'integer' )
		     ->addForeignKey( 'field_value_id', MCS_PREFIX . 'field_values', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'province_id', 'integer' )
		     ->addForeignKey( 'province_id', MCS_PREFIX . 'provinces', 'id', [ 'delete' => 'cascade' ] )
		     ->addIndex( [ 'province_id', 'field_id' ] )
		     ->create();

		$this->table( MCS_PREFIX . 'city_field_values' )
		     ->addColumn( 'field_id', 'integer' )
		     ->addForeignKey( 'field_id', MCS_PREFIX . 'fields', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'field_value_id', 'integer' )
		     ->addForeignKey( 'field_value_id', MCS_PREFIX . 'field_values', 'id', [ 'delete' => 'cascade' ] )
		     ->addColumn( 'city_id', 'integer' )
		     ->addForeignKey( 'city_id', MCS_PREFIX . 'cities', 'id', [ 'delete' => 'cascade' ] )
		     ->addIndex( [ 'city_id', 'field_id' ] )
		     ->create();
	}

}
