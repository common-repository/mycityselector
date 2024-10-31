<?php

namespace Mcs\WpControllers;

use Exception;
use Mcs\WpModels\Cities;
use stdClass;
use WP_REST_Request;

class CitiesController extends BaseController {

	protected $namespace = 'mcs/v1';
	protected $rest_base = 'Cities';

	/**
	 *
	 * @return array Item schema data.
	 *
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => $this->getModelName(),
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				'id'          => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit', 'embed' ],
					'readonly'    => true,
				],
				'title'       => [
					'description' => __( 'Title of city.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'country_id'  => [
					'description' => __( 'Identifier for related country.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit', 'embed' ],
				],
				'province_id' => [
					'description' => __( 'Identifier for related province.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit', 'embed' ],
				],
				'subdomain'   => [
					'description' => __( 'Subdomain of city.' ),
					'type'        => 'string',
				],
				'published'   => [
					'description' => __( 'Publish status of city.' ),
					'type'        => [ 'integer', 'null', 'boolean' ],
				],
				'ordering'    => [
					'description' => __( 'Order of resources' ),
					'type'        => 'integer',
				]
			]
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

		if ( isset( $request['id'] ) ) {
			/** @var Cities $existing_city */
			$existing_city = $this->get_model( $request['id'] );
			if ( is_wp_error( $existing_city ) ) {
				return $existing_city;
			}

			$prepared_model->ID = $existing_city->id;
		}
		$prepared_model->title       = (string) ( $request['title'] ?? $existing_city->title ?? '' );
		$prepared_model->country_id  = (int) ( $request['country_id'] ?? $existing_city->country_id ?? null );
		$prepared_model->province_id = (int) ( $request['province_id'] ?? $existing_city->province_id ?? null );
		$prepared_model->subdomain   = (string) ( $request['subdomain'] ?? $existing_city->subdomain ?? '' );
		$prepared_model->published   = (int) ( $request['published'] ?? $existing_city->published ?? false );
		$prepared_model->ordering    = (int) ( $request['ordering'] ?? $existing_city->ordering ?? null );

		return $prepared_model;
	}

	protected function getModelName() {
		return $this->rest_base;
	}
}
