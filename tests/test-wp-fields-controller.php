<?php

use Mcs\WpModels\Fields;

class testWpFieldsController extends WP_Test_REST_Controller_Testcase {

	/**
	 * @var Fields
	 */
	protected $field;
	/**
	 * @var WP_User|int
	 */
	protected static $user;

	/**
	 * @param $factory
	 *
	 * @throws Exception
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		activate_mcs_plugin();
		self::$user = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
	}

	/**
	 * @throws Exception
	 */
	public static function wpTearDownAfterClass() {
		self::delete_user( self::$user );
	}

	/**
	 * @throws Exception
	 */
	public function setUp() {
		$this->field = Fields::create([
			'name' => 'test',
			'published' => true
		]);
		parent::setUp();
	}

	public function tearDown() {
		$this->field->delete();
		parent::tearDown();
	}


	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/mcs/v1/Fields', $routes );
		$this->assertArrayHasKey( '/mcs/v1/Fields/(?P<id>[\d]+)', $routes );
	}

	public function test_context_param() {
		// Collection.
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Fields' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertArrayHasKey('filter', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('range', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('sort', $data["endpoints"][0]["args"]);
//		$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
//		$this->assertEqualSets( array( 'view', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );

		// Single.
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Fields/' . $this->field->id );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEqualSets( array( 'view', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
	}

	/**
	 * @throws Exception
	 */
	public function test_get_items() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'GET', '/mcs/v1/Fields' );
		$request->set_param( 'context', 'view' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$all_data = $response->get_data();
		$data     = $all_data[0];
		$modelData = Fields::findById( $data['id'] );
		$this->check_model_data( $modelData, $data );
	}

	public function test_get_item() {
		$model_id = $this->field->id;

		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/Fields/%d', $model_id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->check_get_field_response( $response );
	}

	/**
	 * @throws Exception
	 */
	public function test_create_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'POST', '/mcs/v1/Fields' );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_create_model_response( $response );
	}

	//TODO Here
	public function test_update_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'PUT', sprintf( '/mcs/v1/Fields/%d', $this->field->id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_update_model_response( $response );
		$this->field = Fields::findById($this->field->id);
		$this->check_model_data( $this->field, $params );
	}

	/**
	 * @throws Exception
	 */
	public function test_delete_item() {
		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'DELETE', sprintf( '/mcs/v1/Fields/%d', $this->field->id ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$model = Fields::findById( $this->field->id );
//		$this->assertInstanceOf( 'WP_Error', $model );
		$this->assertNull( $model );
//		$this->assertEquals( 404, $model->get_error_code() );
		$this->field = Fields::create([
			'name' => 'test',
			'published' => true
		]);
	}

	public function test_prepare_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/Fields/%d', $this->field->id ) );
		$request->set_query_params( array( 'context' => 'edit' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->check_get_model_response( $response );
	}

	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Fields' );
		$response   = rest_get_server()->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertSame( 3, count( $properties ) );
		$this->assertArrayHasKey( 'id', $properties );
		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'published', $properties );
	}

	/**
	 * @param $response
	 *
	 */
	protected function check_get_field_response( $response ) {
		$this->assertEquals( 200, $response->get_status() );

		$data     = $response->get_data();
		$cityData = Fields::findById( $data['id'] );
		$this->check_model_data( $cityData, $data );
	}

	protected function check_model_data( Fields $model, $data ) {
		if ( isset( $data['id'] ) ) {
			$this->assertEquals( $model->id, $data['id'] );
		}
		$this->assertEquals( $model->name, $data['name'] );
		$this->assertEquals( $model->published, $data['published'] );
	}

	protected function set_model_data( $args = array() ) {
		$defaults = [
			'name' => 'test123',
			'published' => false
		];

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * @param WP_REST_Response $response
	 *
	 * @throws Exception
	 */
	protected function check_create_model_response( WP_REST_Response $response ) {
		$this->assertNotWPError( $response );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 201, $response->get_status() );
		$headers = $response->get_headers();
		$this->assertArrayHasKey( 'Location', $headers );

		$data = $response->get_data();

		$model = Fields::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}

	/**
	 * @param WP_REST_Response $response
	 *
	 */
	protected function check_update_model_response( WP_REST_Response $response ) {
		$this->assertNotWPError( $response );
		$response = rest_ensure_response( $response );

		$this->assertEquals( 200, $response->get_status() );
		$headers = $response->get_headers();
		$this->assertArrayNotHasKey( 'Location', $headers );

		$data  = $response->get_data();
		$model = Fields::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}

	/**
	 * @param WP_REST_Response $response
	 *
	 */
	protected function check_get_model_response( WP_REST_Response $response ) {
		$this->assertNotWPError( $response );
		$response = rest_ensure_response( $response );
		$this->assertEquals( 200, $response->get_status() );

		$data  = $response->get_data();
		$model = Fields::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}
}
