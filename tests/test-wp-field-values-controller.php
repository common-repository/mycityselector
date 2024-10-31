<?php

use Mcs\WpModels\Cities;
use Mcs\WpModels\CityFieldValues;
use Mcs\WpModels\Fields;
use Mcs\WpModels\FieldValues;

class testWpFieldValuesController extends WP_Test_REST_Controller_Testcase {

	/**
	 * @var Fields
	 */
	protected $field;

	/**
	 * @var FieldValues
	 */
	protected $fieldValue;

	protected Cities $city1;
	protected Cities $city2;
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
		$this->field      = Fields::create( [
			'name'      => 'test',
			'published' => true
		] );
		$this->fieldValue = FieldValues::create( [
			'field_id'  => $this->field->id,
			'value'     => 'test',
			'default'   => false,
			'is_ignore' => false
		] );
		$this->city1 = Cities::create([
			'title' => 'some city 1',
			'subdomain' => 'some_city_1',
			'published' => true
		]);
		$this->city2 = Cities::create([
			'title' => 'some city 2',
			'subdomain' => 'some_city_2',
			'published' => true
		]);
		parent::setUp();
	}

	/**
	 * @throws Exception
	 */
	public function tearDown() {
		$this->city1->delete();
		$this->city2->delete();
		foreach ( $this->field->getFieldValues() as $fieldValue ) {
			$fieldValue->delete();
		}
		$this->field->delete();
		parent::tearDown();
	}


	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/mcs/v1/FieldValues', $routes );
		$this->assertArrayHasKey( '/mcs/v1/FieldValues/(?P<id>[\d]+)', $routes );
	}

	public function test_context_param() {
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/FieldValues' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertArrayHasKey('filter', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('range', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('sort', $data["endpoints"][0]["args"]);
		//$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		//$this->assertEqualSets( array( 'view', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );


		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/FieldValues/' . $this->field->id );
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

		$request = new WP_REST_Request( 'GET', '/mcs/v1/FieldValues' );
		$request->set_param( 'context', 'view' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$all_data  = $response->get_data();
		$data      = $all_data[0];
		$modelData = FieldValues::findById( $data['id'] );
		$this->check_model_data( $modelData, $data );
	}

	public function test_get_item() {
		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/FieldValues/%d', $this->fieldValue->id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->check_get_field_response( $response );
	}

	/**
	 * @throws Exception
	 */
	public function test_create_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'POST', '/mcs/v1/FieldValues' );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );
		$this->check_create_model_response( $response );
		FieldValues::findById( $response->get_data()['id'] )->delete();
	}

	public function test_update_item() {
		wp_set_current_user( self::$user );
		$request = new WP_REST_Request( 'PUT', sprintf( '/mcs/v1/FieldValues/%d', $this->fieldValue->id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_update_model_response( $response );
		$this->fieldValue = FieldValues::findById( $this->fieldValue->id );
		$this->check_model_data( $this->fieldValue, $params );

		$cityFieldValue1 = CityFieldValues::findForField($this->field, $this->city1);
		$this->assertNotEmpty($cityFieldValue1);
		$cityFieldValue1->delete();

		$cityFieldValue2 = CityFieldValues::findForField($this->field, $this->city2);
		$this->assertNotEmpty($cityFieldValue2);
		$cityFieldValue2->delete();
	}

	/**
	 * @throws Exception
	 */
	public function test_delete_item() {
		wp_set_current_user( self::$user );
		$request  = new WP_REST_Request( 'DELETE', sprintf( '/mcs/v1/FieldValues/%d', $this->fieldValue->id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
		$model = FieldValues::findById( $this->fieldValue->id );
		$this->assertNull( $model );
//		$this->assertEquals( 404, $model->get_error_code() );
		$this->fieldValue = FieldValues::create( [
			'field_id'  => $this->field->id,
			'value'     => 'test',
			'default'   => false,
			'is_ignore' => false
		] );
	}

	public function test_prepare_item() {
		wp_set_current_user( self::$user );
		$request = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/FieldValues/%d', $this->fieldValue->id ) );
		$request->set_query_params( array( 'context' => 'edit' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->check_get_model_response( $response );
	}

	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/mcs/v1/FieldValues' );
		$response   = rest_get_server()->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertSame( 6, count( $properties ) );
		$this->assertArrayHasKey( 'id', $properties );
		$this->assertArrayHasKey( 'field_id', $properties );
		$this->assertArrayHasKey( 'value', $properties );
		$this->assertArrayHasKey( 'default', $properties );
		$this->assertArrayHasKey( 'is_ignore', $properties );
	}

	/**
	 * @param $response
	 *
	 */
	protected function check_get_field_response( $response ) {
		$this->assertEquals( 200, $response->get_status() );
		$data     = $response->get_data();
		$cityData = FieldValues::findById( $data['id'] );
		$this->check_model_data( $cityData, $data );
	}

	protected function check_model_data( FieldValues $model, $data ) {
		if ( isset( $data['id'] ) ) {
			$this->assertEquals( $model->id, $data['id'] );
		}
		$this->assertEquals( $model->field_id, $data['field_id'] );
		$this->assertEquals( $model->value, $data['value'] );
		$this->assertEquals( $model->default, $data['default'] );
		$this->assertEquals( $model->is_ignore, $data['is_ignore'] );
	}

	protected function set_model_data( $args = array() ) {
		$defaults = [
			'field_id'  => $this->field->id,
			'value'     => 'test test test',
			'default'   => true,
			'is_ignore' => true,
			'city_ids' => [
				$this->city1->getId(),
				$this->city2->getId(),
			]
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

		$model = FieldValues::findById( $data['id'] );
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
		$model = FieldValues::findById( $data['id'] );
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
		$model = FieldValues::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}
}
