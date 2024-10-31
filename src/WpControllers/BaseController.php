<?php


namespace Mcs\WpControllers;

use Exception;
use Mcs\Interfaces\ModelInterface;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

abstract class BaseController extends WP_REST_Controller {

	/**
	 * @return string
	 */
	abstract protected function getModelName();


	/**
	 * @param $id
	 *
	 * @return ModelInterface|WP_Error
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

		/** @var ModelInterface $modelClass */
		$modelClass = 'Mcs\WpModels\\' . $modelName;
		$model      = $modelClass::findById( (int) $id );
		if ( empty( $model ) || empty( $model->id ) ) {
			return $error;
		}

		return $model;
	}


	// Register our routes.
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			// Here we register the readable endpoint for collections.
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema(),
			],
			// Register our schema callback.
			'schema' => array( $this, 'get_public_item_schema' ),
		] );

		$modelName = $this->getModelName();
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args'   => [
					'id' => [
						'description' => __( "Unique identifier for the {$modelName}." ),
						'type'        => 'integer',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'args'                => [
						'force' => [
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( "Required to be true, as {$this->rest_base} do not support trashing." ),
						],
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( "You cannot view the {$this->rest_base} resource." ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( "You cannot create the {$this->rest_base} resource." ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * @return bool|WP_Error
	 */
	protected function check_delete_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( "You cannot delete the {$this->rest_base} resource." ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Check permissions for the posts.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Prepare a response for inserting into a collection of responses.
	 *
	 * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
	 *
	 * @param WP_REST_Response $response Response object.
	 *
	 * @return array|WP_REST_Response Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}

	public function get_collection_params() {
		//$query_params['context']['default'] = 'view';
		$query_params['range']  = array(
			'description' => __( 'Range the result set by a specific number of items.' ),
			'type'        => 'json',
		);
		$query_params['sort']   = array(
			'description' => __( 'Order sort attribute ascending or descending. ["id", "ASC"]' ),
			'type'        => 'json',
		);
		$query_params['filter'] = array(
			'default'     => null,
			'description' => __( 'Filter collection by property.' ),
			'type'        => 'json',
		);

		return $query_params;
	}

	/**
	 * @param mixed $item
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [];
		foreach ( $item->getProperties() as $property ) {
			switch ( $property ) {
				case 'ordering':
					$data[ $property ] = (int) $item->$property;
					break;
				case 'published':
				case 'default':
					$data[ $property ] = (bool) $item->$property;
					break;
				default:
					$data[ $property ] = $item->$property;
			}
		}

		return rest_ensure_response( $data );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 * @throws Exception
	 */
	public function update_item( $request ) {
		$valid_check = $this->get_model( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}

		$model     = $valid_check;
		$modelData = $this->prepare_item_for_database( $request );

		try {
			$model->update( (array) $modelData );
		} catch ( Exception $exception ) {
			return new WP_Error(
				'',
				'Error',
				array( 'status' => 404 )
			);
		}

		$response = $this->prepare_item_for_response( $model, $request );

		return rest_ensure_response( $response );
	}

	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			$modelName = $this->getModelName();

			return new WP_Error( 'rest_forbidden', esc_html__( "You cannot update the {$modelName} resource." ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Deletes a single post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 * @since 4.7.0
	 *
	 */
	public function delete_item( $request ) {
		$model = $this->get_model( $request['id'] );
		if ( is_wp_error( $model ) ) {
			return $model;
		}

		$modelName = $this->getModelName();

		if ( ! $this->check_delete_permission() ) {
			return new WP_Error(
				'rest_user_cannot_delete_' . $modelName,
				__( "Sorry, you are not allowed to delete this {$modelName}." ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$previous = $this->prepare_item_for_response( $model, $request );
		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $previous->get_data(),
			)
		);

		try {
			$model->delete();
		} catch ( Exception $exception ) {
			return new WP_Error(
				'rest_cannot_delete',
				__( "The {$modelName} cannot be deleted." ),
				array( 'status' => 500 )
			);
		}

		return $response;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|ModelInterface|true|WP_Error
	 * @throws Exception
	 */
	public function delete_item_permissions_check( $request ) {
		$model = $this->get_model( $request['id'] );
		if ( is_wp_error( $model ) ) {
			return $model;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( "You cannot delete the {$this->rest_base} resource." ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;
		/** @var ModelInterface $modelClass */
		$modelClass = 'Mcs\WpModels\\' . $this->getModelName();

		$filter      = $request['filter'];
		$whereString = '';
		if ( $filter ) {
			$whereString = $this->get_where_string( $filter );
		}

		$query = "SELECT count(*) FROM " . $modelClass::getTableName() . " {$whereString}";
		$total = (int) $wpdb->get_var( $query );

		$range    = $request['range'];
		$per_page = 10;
		$start    = 0;
		$end      = $start + $per_page;
		if ( $range ) {
			$rangeData = json_decode( $range, true );
			$start     = (int) $rangeData[0] ?? $start;
			$end       = (int) $rangeData[1] ?? $end;
			$per_page  = $end - $start + 1;
		}
		$max_pages = (int) ceil( $total / $per_page );

		$sort       = $request['sort'];
		$sortString = '';
		if ( $sort ) {
			$sortData     = json_decode( $sort, true );
			$order        = strtoupper( $sortData[1] ) == 'DESC' ? 'DESC' : '';
			$sortProperty = $wpdb->_real_escape( $sortData[0] );
			$sortString   = " ORDER BY `{$sortProperty}` {$order}";
		}

		$modelsData = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM " . $modelClass::getTableName() . " {$whereString} {$sortString}  LIMIT %d, %d",
			$start, $per_page
		), 'ARRAY_A' );
		$models     = [];
		foreach ( $modelsData as $modelData ) {
			$model = new $modelClass();
			$model->fillProperties( $modelData );
			$models[] = $model;
		}

		$data = [];

		foreach ( $models as $model ) {
			$response = $this->prepare_item_for_response( $model, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		// Return all of our comment response data.
		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $max_pages );
		$response->header( 'Content-Range', "{$this->rest_base} {$start}-{$end}/{$total}" );

		return $response;
	}

	/**
	 * @param $filter
	 *
	 * @return string
	 */
	protected function get_where_string( $filter ) {
		$filterData = json_decode( $filter, true );
		if ( gettype( $filterData ) == 'array' ) {
			global $wpdb;
			$wheres = [];
			foreach ( $filterData as $propertyName => $propertyValue ) {
				switch ( gettype( $propertyValue ) ) {
					case 'integer':
						$wheres[] = $wpdb->prepare( "{$propertyName} = %d", $propertyValue );
						break;
					case 'string':
						$wheres[] = $wpdb->prepare( "{$propertyName} COLLATE UTF8_GENERAL_CI LIKE %s", $propertyValue . '%' );
						break;
					case 'array':
						$values = [];
						foreach ( $propertyValue as $propertyValueValue ) {
							$values[] = (int) $propertyValueValue;
						}
						if ( count( $values ) ) {
							$values   = implode( ',', $values );
							$wheres[] = $wpdb->_real_escape( $propertyName ) . " IN ({$values})";
						}
						break;
					case 'boolean':
						if ( $propertyValue ) {
							$wheres[] = '`' . $propertyName . '` IS TRUE';
						} else {
							$wheres[] = "NOT `{$propertyName}`";
						}
						break;
				}
			}
			if ( count( $wheres ) ) {
				return 'WHERE ' . implode( ' AND ', $wheres );
			}
		}

		return '';
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @throws Exception
	 */
	public function create_item( $request ) {
		$modelName = $this->getModelName();
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error(
				"rest_{$modelName}_exists",
				__( "Cannot create existing {$modelName}." ),
				array( 'status' => 400 )
			);
		}

		$preparedData = (array) $this->prepare_item_for_database( $request );
		/** @var ModelInterface $className */
		$className    = 'Mcs\WpModels\\' . $modelName;
		try {
			$model = $className::create( $preparedData );
		} catch ( Exception $exception ) {
			$error = new WP_Error();
			$error->add_data( array( 'status' => 400 ), $exception->getCode() );

			return $error;
		}

		if ( empty( $model ) ) {
			return rest_ensure_response( array() );
		}

		$id    = $model->id;
		$model = $this->prepare_item_for_response( $model, $request );

		$response = rest_ensure_response( $model );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

		return $response;
	}

	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @throws Exception
	 */
	public function get_item( $request ) {
		/** @var ModelInterface $className */
		$className = 'Mcs\WpModels\\' . $this->getModelName();
		$id        = (int) $request['id'];
		$model     = $className::findById( $id );

		if ( empty( $model ) ) {
			return rest_ensure_response( array() );
		}

		$model = $this->prepare_item_for_response( $model, $request );

		// Return all of our post response data.
		return rest_ensure_response( $model );
	}
}
