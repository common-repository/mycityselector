<?php

use Mcs\WpModels\Cities;
use Mcs\WpModels\CityFieldValues;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Fields;
use Mcs\WpModels\FieldValues;
use Mcs\WpModels\Provinces;

class testWpCityFieldValuesController extends WP_Test_REST_Controller_Testcase {

	/**
	 * @var Fields
	 */
	protected $field;

	/**
	 * @var FieldValues
	 */
	protected $fieldValue;

	/**
	 * @var FieldValues
	 */
	protected $fieldValue2;

	/**
	 * @var Countries;
	 */
	protected $country;

	/**
	 * @var Provinces
	 */
	protected $province;

	/**
	 * @var Cities
	 */
	protected $city;

	/**
	 * @var CityFieldValues
	 */
	protected $cityFieldValue;

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
		$this->country = Countries::create( [
			'subdomain' => 'test',
			'code'      => 'ru',
			'domain'    => 'ru'
		] );

		$this->province = Provinces::create( [
			'country_id' => $this->country->id,
			'subdomain'  => 'test'
		] );

		$this->city = Cities::create( [
			'title'       => 'city title',
			'country_id'  => $this->country->id,
			'province_id' => $this->province->id,
			'subdomain'   => 'city-subdomain',
			'published'   => true,
		] );

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

		$this->fieldValue2 = FieldValues::create( [
			'field_id'  => $this->field->id,
			'value'     => 'test2',
			'default'   => false,
			'is_ignore' => false
		] );

		$this->cityFieldValue = CityFieldValues::create( [
			'field_id' => $this->field->getId(),
			'field_value_id' => $this->fieldValue->getId(),
			'city_id'        => $this->city->getId()
		] );

		parent::setUp();
	}

	/**
	 * @throws Exception
	 */
	public function tearDown() {
		$this->cityFieldValue->delete();
		$this->fieldValue->delete();
		$this->fieldValue2->delete();
		foreach ( $this->field->getFieldValues() as $field_value ) {
			$field_value->delete();
		}
		$this->field->delete();
		$this->city->delete();
		$this->province->delete();
		$this->country->delete();
		parent::tearDown();
	}


	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/mcs/v1/CityFieldValues', $routes );
		$this->assertArrayHasKey( '/mcs/v1/CityFieldValues/(?P<id>[\d]+)', $routes );
	}

	public function test_context_param() {
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/CityFieldValues' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertArrayHasKey('filter', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('range', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('sort', $data["endpoints"][0]["args"]);
		//$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		//$this->assertEqualSets( array( 'view', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );


		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/CityFieldValues/' . $this->field->id );
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

		$request = new WP_REST_Request( 'GET', '/mcs/v1/CityFieldValues' );
		$request->set_param( 'context', 'view' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$all_data  = $response->get_data();
		$data      = $all_data[0];
		$modelData = CityFieldValues::findById( $data['id'] );
		$this->check_model_data( $modelData, $data );
	}

	public function test_get_item() {
		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/CityFieldValues/%d', $this->cityFieldValue->id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->check_get_field_response( $response );
	}

	/**
	 * @throws Exception
	 */
	public function test_create_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'POST', '/mcs/v1/CityFieldValues' );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );
		$this->check_create_model_response( $response );
		CityFieldValues::findById( $response->get_data()['id'] )->delete();
	}

	public function test_update_item() {
		wp_set_current_user( self::$user );
		$request = new WP_REST_Request( 'PUT', sprintf( '/mcs/v1/CityFieldValues/%d', $this->cityFieldValue->id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_update_model_response( $response );
		$this->cityFieldValue = CityFieldValues::findById( $this->cityFieldValue->id );
		$this->check_model_data( $this->cityFieldValue, $params );
	}

	/**
	 * @throws Exception
	 */
	public function test_delete_item() {
		wp_set_current_user( self::$user );
		$request  = new WP_REST_Request( 'DELETE', sprintf( '/mcs/v1/CityFieldValues/%d', $this->cityFieldValue->id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
		$model = CityFieldValues::findById( $this->cityFieldValue->id );
		$this->assertNull( $model );
//		$this->assertEquals( 404, $model->get_error_code() );
		$this->cityFieldValue = CityFieldValues::create( $this->set_model_data() );
	}

	public function test_prepare_item() {
		wp_set_current_user( self::$user );
		$request = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/CityFieldValues/%d', $this->cityFieldValue->id ) );
		$request->set_query_params( array( 'context' => 'edit' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->check_get_model_response( $response );
	}

	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/mcs/v1/CityFieldValues' );
		$response   = rest_get_server()->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertSame( 3, count( $properties ) );
		$this->assertArrayHasKey( 'id', $properties );
		$this->assertArrayHasKey( 'field_value_id', $properties );
		$this->assertArrayHasKey( 'city_id', $properties );
	}

	/**
	 * @param $response
	 *
	 */
	protected function check_get_field_response( $response ) {
		$this->assertEquals( 200, $response->get_status() );
		$data     = $response->get_data();
		$cityData = CityFieldValues::findById( $data['id'] );
		$this->check_model_data( $cityData, $data );
	}

	protected function check_model_data( CityFieldValues $model, array $data ) {
		if ( isset( $data['id'] ) ) {
			$this->assertEquals( $model->id, $data['id'] );
		}
		$this->assertEquals( $model->field_value_id, $data['field_value_id'] );
		$this->assertEquals( $model->city_id, $data['city_id'] );
	}

	protected function set_model_data( $args = array() ) {
		$defaults = [
			'field_id' => $this->field->getId(),
			'field_value_id' => $this->fieldValue2->id,
			'city_id'        => $this->city->id
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

		$model = CityFieldValues::findById( $data['id'] );
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
		$model = CityFieldValues::findById( $data['id'] );
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
		$model = CityFieldValues::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}
}
