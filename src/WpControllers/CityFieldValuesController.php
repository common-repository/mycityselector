<?php

namespace Mcs\WpControllers;

use Exception;
use Mcs\WpModels\CityFieldValues;
use stdClass;
use WP_REST_Request;

/**
 * Class CityFieldsValuesController
 * @package Mcs\WpControllers
 * @method CityFieldValues get_model( $id )
 */
class CityFieldValuesController extends BaseController {

	protected $namespace = 'mcs/v1';
	protected $rest_base = 'CityFieldValues';

	/**
	 *
	 * @return array Item schema data.
	 *
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}
		$modelName    = $this->getModelName();
		$this->schema = [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => $modelName,
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				'id'             => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'field_value_id' => [
					'description' => __( 'ID of related field value.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
				],
				'city_id'        => [
					'description' => __( 'ID of related city.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
				]
			],
		];

		return $this->schema;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return object
	 * @throws Exception
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_model = new stdClass();

		$existing_model = null;
		if ( isset( $request['id'] ) ) {
			$existing_model = $this->get_model( $request['id'] );
			if ( is_wp_error( $existing_model ) ) {
				return $existing_model;
			}

			$prepared_model->ID = $existing_model->id;
		}

		$prepared_model->field_id = (int) ( $request['field_id'] ?? ( $existing_model->getFieldId() ?? null ) );
		$prepared_model->field_value_id = (int) ( $request['field_value_id'] ?? ( $existing_model->getFieldValueId() ?? null ) );
		$prepared_model->city_id        = (int) ( $request['city_id'] ?? ( $existing_model->getCityId() ?? '' ) );

		return $prepared_model;
	}

	protected function getModelName() {
		return $this->rest_base;
	}
}

