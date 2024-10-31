<?php

namespace Mcs\WpControllers;

use Exception;
use Mcs\Interfaces\ModelInterface;
use Mcs\WpModels\Provinces;
use stdClass;
use WP_Error;
use WP_REST_Request;

class ProvincesController extends BaseController {

	protected $namespace = 'mcs/v1';
	protected $rest_base = 'Provinces';

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
				'id'         => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'title'      => [
					'description' => __( 'Title of province.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'country_id' => [
					'description' => __( 'Country id.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ]
				],
				'subdomain'  => [
					'description' => __( 'Subdomain of province.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'published'  => [
					'description' => __( 'Publish status of province.' ),
					'type'        => [ 'integer', 'boolean' ],
					'context'     => [ 'view', 'edit' ],
				],
				'ordering'   => [
					'description' => __( 'Order of provinces.' ),
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

		if ( isset( $request['id'] ) ) {
			$existing_model = $this->get_model( $request['id'] );
			if ( is_wp_error( $existing_model ) ) {
				return $existing_model;
			}

			$prepared_model->ID = $existing_model->id;
		}

		$prepared_model->title      = (string) ($request['title'] ?? $existing_model->title ?? '') ;
		$prepared_model->country_id = (int) ($request['country_id'] ?? $existing_model->country_id ?? null);
		$prepared_model->subdomain  = (string) ($request['subdomain'] ?? $existing_model->subdomain ?? '');
		$prepared_model->published  = (int) ($request['published'] ?? $existing_model->published ?? false);
		$prepared_model->ordering   = (int) ($request['ordering'] ?? $existing_model->ordering ?? null);

		return $prepared_model;
	}

	/**
	 * @param $id
	 *
	 * @return ModelInterface|Provinces|WP_Error
	 * @throws Exception
	 */
	protected function get_model( $id ) {
		$modelName = $this->getModelName();
		$error     = new WP_Error(
			"rest_{$modelName}_invalid_id",
			__( "Invalid {$modelName} ID." ),
			array( 'status' => 404 )
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		$model = Provinces::findById( (int) $id );
		if ( empty( $model ) || empty( $model->id ) ) {
			return $error;
		}

		return $model;
	}


	protected function getModelName() {
		return $this->rest_base;
	}
}
