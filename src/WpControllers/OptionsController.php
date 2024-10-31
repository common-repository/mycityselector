<?php

namespace Mcs\WpControllers;

use Mcs\Interfaces\DataInterface;
use Mcs\WpModels\Cities;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Options;
use Mcs\WpModels\Provinces;
use WP_Error;

class OptionsController extends BaseController {

	protected $namespace = 'mcs/v1';
	protected $rest_base = 'Options';

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
				'id'                      => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'base_domain'             => [
					'description' => __( 'Base domain of your site, f.e.: example.com' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ]
				],
				'default_city_id'         => [
					'description' => __( 'Default city id' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ]
				],
				'seo_mode'                => [
					'description' => __( 'SEO mode' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ],
				],
				'country_choose_enabled'  => [
					'description' => __( 'Country choose enabled' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ]
				],
				'province_choose_enabled' => [
					'description' => __( 'Province choose enabled' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ],
				],
				'ask_mode'                => [
					'description' => __( 'Ask mode' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit' ]
				],
				'redirect_next_visits'    => [
					'description' => __( 'Redirect on next visits' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ]
				],
				'log_enabled'             => [
					'description' => __( 'Logging enabled' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ]
				],
				'debug_enabled'           => [
					'description' => __( 'Debug enabled' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ]
				]
			]
		];

		return $this->schema;
	}

	protected function getModelName() {
		return $this->rest_base;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $request ) {
		return rest_ensure_response( ( new Options() )->toArray() );
	}

	/**
	 * @inheritDoc
	 */
	public function update_item( $request ) {
		$options = new Options();
//		$domain  = filter_var( $request['base_domain'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME );
//		if ( ! $domain ) {
//			return new WP_REST_Response( [
//				'message' => 'Wrong domain name'
//			], 400 );
//		}
//		$options->setBaseDomain( $domain );

		switch ( $request['default_location_type'] ) {
			case DataInterface::LOCATION_TYPE_CITY:
				$model = Cities::findById( $request['default_location_id'] );
				if ( ! $model || ! $model->published ) {
					return new WP_Error( 400, 'Wrong default city' );
				}
				$options->updateDefaultLocationId( $model->id );
				$options->updateDefaultLocationType( $request['default_location_type'] );
				$options->setDefaultLocation( $model );
				break;
			case DataInterface::LOCATION_TYPE_PROVINCE:
				$model = Provinces::findById( $request['default_location_id'] );
				if ( ! $model || ! $model->published ) {
					return new WP_Error( 400, 'Wrong default province / state' );
				}
				$options->updateDefaultLocationId( $model->id );
				$options->updateDefaultLocationType( $request['default_location_type'] );
				$options->setDefaultLocation( $model );
				break;
			case DataInterface::LOCATION_TYPE_COUNTRY:
				$model = Countries::findById( $request['default_location_id'] );
				if ( ! $model || ! $model->published ) {
					return new WP_Error( 400, 'Wrong default country' );
				}
				$options->updateDefaultLocationId( $model->id );
				$options->updateDefaultLocationType( $request['default_location_type'] );
				$options->setDefaultLocation( $model );
				break;
		}

		$options->setSeoMode( (int) $request['seo_mode'] );
		$options->setCountryChooseEnabled( (bool) $request['country_choose_enabled'] );
		$options->setProvinceChooseEnabled( (bool) $request['province_choose_enabled'] );
		$options->setAskMode( (int) $request['ask_mode'] );
		$options->setRedirectNextVisits( (bool) $request['redirect_next_visits'] );
		$options->setLogEnabled( (bool) $request['log_enabled'] );
		$options->setDebugEnabled( (bool) $request['debug_enabled'] );

		return rest_ensure_response( $options->toArray() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_items( $request ) {
		$data     = [];
		$response = rest_ensure_response( ( new Options() )->toArray() );
		$data[]   = $this->prepare_response_for_collection( $response );


		// Return all of our comment response data.
		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', 1 );
		$response->header( 'X-WP-TotalPages', 1 );
		$response->header( 'Content-Range', "$this->rest_base {0}-{0}/{1}" );

		return $response;
	}
}

