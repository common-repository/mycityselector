<?php

namespace Mcs\WpControllers;

use Exception;
use Mcs\WpModels\Countries;
use stdClass;
use WP_REST_Request;

/**
 * Class CountriesController
 * @package Mcs\WpControllers
 * @method Countries get_model( $id )
 */
class CountriesController extends BaseController {

	protected $namespace = 'mcs/v1';
	protected $rest_base = 'Countries';

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
				'id'              => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'title'           => [
					'description' => __( 'Title of country.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'subdomain'       => [
					'description' => __( 'Subdomain of country.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'published'       => [
					'description' => __( 'Publish status of country.' ),
					'type'        => [ 'integer', 'null', 'boolean' ],
					'context'     => [ 'view', 'edit' ],
				],
				'ordering'        => [
					'description' => __( 'Order of countries.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
				],
				'code'            => [
					'description' => __( 'Country code.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'domain'          => [
					'description' => __( 'Domain of country.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				/*'lat'             => [
					'description' => __( 'Latitude of country.' ),
					'type'        => 'number',
					'context'     => [ 'view', 'edit' ]
				],
				'lng'             => [
					'description' => __( 'Longitude of country.' ),
					'type'        => 'number',
					'context'     => [ 'view', 'edit' ]
				],*/
				'default_city_id' => [
					'description' => __( 'Default city id.' ),
					'type'        => [ 'integer' ],
					'context'     => [ 'view', 'edit' ]
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
		$prepared_country = new stdClass();

		$existing_country = null;
		if ( isset( $request['id'] ) ) {
			$existing_country = $this->get_model( $request['id'] );
			if ( is_wp_error( $existing_country ) ) {
				return $existing_country;
			}

			$prepared_country->ID = $existing_country->id;
		}

		$prepared_country->title           = (string) ( $request['title'] ?? ( $existing_country->title ?? '' ) );
		$prepared_country->subdomain       = (string) ( $request['subdomain'] ?? ( $existing_country->subdomain ?? '' ) );
		$prepared_country->published       = (int) ( $request['published'] ?? ( $existing_country->published ?? 0 ) );
		$prepared_country->ordering        = (int) ( $request['ordering'] ?? ( $existing_country->ordering ?? 100 ) );
		$prepared_country->code            = (string) ( $request['code'] ?? ( $existing_country->code ?? '' ) );
		$prepared_country->domain          = (string) ( $request['domain'] ?? ( $existing_country->domain ?? '' ) );
		$prepared_country->default_city_id = $request['default_city_id'] ?? ( $existing_country->default_city_id ?? null );

		return $prepared_country;
	}

	protected function getModelName() {
		return $this->rest_base;
	}
}
