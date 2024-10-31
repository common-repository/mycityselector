<?php


use Mcs\WpModels\Cities;
use Mcs\WpModels\Countries;
use Mcs\WpModels\Provinces;

class testWpCitiesController extends WP_Test_REST_Controller_Testcase {
	/**
	 * @var Countries
	 */
	protected static $country;

	/**
	 * @var Provinces
	 */
	protected static $province;

	/**
	 * @var Cities
	 */
	protected static $city;

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

		self::$country = Countries::create( [
			'subdomain' => 'test',
			'code'      => 'ru',
			'domain'    => 'ru'
		] );

		self::$province = Provinces::create( [
			'country_id' => self::$country->id,
			'subdomain'  => 'test'
		] );

		self::$city = Cities::create( [
			'subdomain' => 'test',
		] );
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
		self::$city->delete();
		self::$province->delete();
		self::$country->delete();
	}


	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/mcs/v1/Cities', $routes );
		$this->assertArrayHasKey( '/mcs/v1/Cities/(?P<id>[\d]+)', $routes );
	}

	public function test_context_param() {
		// Collection.
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Cities' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertArrayHasKey('filter', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('range', $data["endpoints"][0]["args"]);
		$this->assertArrayHasKey('sort', $data["endpoints"][0]["args"]);
		//$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		//$this->assertEqualSets( array( 'view', 'embed', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );

		// Single.
		$request  = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Cities/' . self::$city->id );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 'view', $data['endpoints'][0]['args']['context']['default'] );
		$this->assertEqualSets( array( 'view', 'embed', 'edit' ), $data['endpoints'][0]['args']['context']['enum'] );
	}

	/**
	 * @throws Exception
	 */
	public function test_get_items() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'GET', '/mcs/v1/Cities' );
		$request->set_param( 'context', 'view' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$all_data = $response->get_data();
		$data     = $all_data[0];
		$cityData = Cities::findById( $data['id'] );
		//$userdata = get_userdata( $data['id'] );
		$this->check_model_data( $cityData, $data );
	}

	public function test_get_item() {
		$city_id = self::$city->id;

		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/Cities/%d', $city_id ) );
		$response = rest_get_server()->dispatch( $request );
		$this->check_get_city_response( $response );
	}

	/**
	 * @throws Exception
	 */
	public function test_create_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'POST', '/mcs/v1/Cities' );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_create_model_response( $response );
	}

	public function test_update_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'PUT', sprintf( '/mcs/v1/Cities/%d', self::$city->id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = $this->set_model_data();
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$this->check_update_model_response( $response );
		$model = Cities::findById( self::$city->id );
		$this->check_model_data( $model, $params );
	}

	public function test_delete_item() {
		wp_set_current_user( self::$user );

		$request  = new WP_REST_Request( 'DELETE', sprintf( '/mcs/v1/Cities/%d', self::$city->id ) );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );

		$model = Cities::findById( self::$city->id );
		$this->assertNull( $model );
//		$this->assertEquals( 404, $model->get_error_code() );
	}

	public function test_prepare_item() {
		wp_set_current_user( self::$user );

		$request = new WP_REST_Request( 'GET', sprintf( '/mcs/v1/Cities/%d', self::$city->id ) );
		$request->set_query_params( array( 'context' => 'edit' ) );
		$response = rest_get_server()->dispatch( $request );

		$this->check_get_model_response( $response );
	}

	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/mcs/v1/Cities' );
		$response   = rest_get_server()->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertSame( 7, count( $properties ) );
		$this->assertArrayHasKey( 'id', $properties );
		$this->assertArrayHasKey( 'title', $properties );
		$this->assertArrayHasKey( 'country_id', $properties );
		$this->assertArrayHasKey( 'province_id', $properties );
		$this->assertArrayHasKey( 'subdomain', $properties );
		$this->assertArrayHasKey( 'published', $properties );
		$this->assertArrayHasKey( 'ordering', $properties );
	}

	/**
	 * @param $response
	 *
	 */
	protected function check_get_city_response( $response ) {
		$this->assertEquals( 200, $response->get_status() );

		$data     = $response->get_data();
		$cityData = Cities::findById( $data['id'] );
		$this->check_model_data( $cityData, $data );
	}

	protected function check_model_data( Cities $model, $data ) {
		if ( isset( $data['id'] ) ) {
			$this->assertEquals( $model->id, $data['id'] );
		}
		$this->assertEquals( $model->title, $data['title'] );
		$this->assertEquals( $model->country_id, $data['country_id'] );
		$this->assertEquals( $model->province_id, $data['province_id'] );
		$this->assertEquals( $model->subdomain, $data['subdomain'] );
		$this->assertEquals( $model->published, $data['published'] );
		$this->assertEquals( $model->ordering, $data['ordering'] );
	}

	protected function set_model_data( $args = array() ) {
		$defaults = array(
			'title'       => 'Test City',
			'country_id'  => self::$country->id,
			'province_id' => self::$province->id,
			'subdomain'   => 'test-city-subdomain',
			'published'   => 1,
			'ordering'    => 10,
		);

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

		$model = Cities::findById( $data['id'] );
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
		$model = Cities::findById( $data['id'] );
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
		$model = Cities::findById( $data['id'] );
		$this->check_model_data( $model, $data );
	}
}
