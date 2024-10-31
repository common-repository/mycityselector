<?php

use Mcs\Interfaces\OptionsInterface;
use Mcs\WpControllers\CitiesController;
use Mcs\WpControllers\CityFieldValuesController;
use Mcs\WpControllers\CountriesController;
use Mcs\WpControllers\CountryFieldValuesController;
use Mcs\WpControllers\FieldsController;
use Mcs\WpControllers\FieldValuesController;
use Mcs\WpControllers\OptionsController;
use Mcs\WpControllers\ProvinceFieldValuesController;
use Mcs\WpControllers\ProvincesController;
use Mcs\WpModels\Data;
use Mcs\WpModels\Options;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

function activate_mcs_plugin() {
	mcs_migrate();
}

function mcs_migrate() {
	$cwd = getcwd();
	chdir( __DIR__ );
	$phinx = new PhinxApplication();
	$phinx->setAutoExit( false );
	$phinx->setCatchExceptions( false );
	try {
		$phinx->run( new StringInput( 'migrate' ), new ConsoleOutput() );
	} catch ( Exception $exception ) {

	}
	chdir( $cwd );
}

function deactivate_mcs_plugin() {

}

function uninstall_mcs_plugin() {

}

function mcs_register_options() {
	// Register a new setting for "mcs" page.
	register_setting( 'mcs', 'mcs_base_domain', [
		'type'        => 'string',
		'description' => 'Base domain of your site, f.e.: example.com',
		'default'     => ''
	] );
	register_setting( 'mcs', 'mcs_default_city_id', [
		'type'        => 'integer',
		'description' => 'Default city id',
		'default'     => null
	] );
	register_setting( 'mcs', 'mcs_seo_mode', [
		'type'        => 'integer',
		'description' => 'SEO mode',
		'default'     => OptionsInterface::SEO_MODE_COOKIE
	] );
	register_setting( 'mcs', 'mcs_country_choose_enabled', [
		'type'        => 'boolean',
		'description' => 'Country choose enabled',
		'default'     => false
	] );
	register_setting( 'mcs', 'mcs_province_choose_enabled', [
		'type'        => 'boolean',
		'description' => 'Province choose enabled',
		'default'     => false
	] );
	register_setting( 'mcs', 'mcs_ask_mode', [
		'type'        => 'integer',
		'description' => 'Ask mode',
		'default'     => OptionsInterface::ASK_MODE_DIALOG
	] );
	register_setting( 'mcs', 'mcs_redirect_next_visits', [
		'type'        => 'boolean',
		'description' => 'Redirect on next visits',
		'default'     => false
	] );
	register_setting( 'mcs', 'mcs_log_enabled', [
		'type'        => 'boolean',
		'description' => 'Logging enabled',
		'default'     => false
	] );
	register_setting( 'mcs', 'mcs_debug_enabled', [
		'type'        => 'boolean',
		'description' => 'Debug enabled',
		'default'     => false
	] );
}

function mcs_remove_admin_css( $styles ) {
	if ( ! empty( $_REQUEST['page'] ) && str_starts_with( $_REQUEST['page'], 'mycityselector' ) ) {
		return array_filter( $styles, function ( $value ) {
			return $value !== 'forms';
		} );
	}

	return $styles;
}

function mcs_admin_enqueue_scripts() {
	$assetFile = include( plugin_dir_path( __FILE__ ) . 'admin/build/index.asset.php' );
	wp_enqueue_script( 'mcs-bundle', plugins_url( 'admin/build/index.js', __FILE__ ),
		$assetFile['dependencies'],
		$assetFile['version'],
		true );
	wp_localize_script( 'mcs-bundle', 'wpApiSettings', [
		'root'  => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' )
	] );
	add_filter( 'print_styles_array', 'mcs_remove_admin_css' );
}

function mcs_options_page() {
	add_menu_page( 'MyCitySelector Plugin',
		'MyCitySelector',
		'manage_options',
		plugin_dir_path( __FILE__ ),
		'mcs_main_html',
		plugin_dir_url( __FILE__ ) . 'assets/icon.svg'
	);
}

function mcs_process() {
	if ( ! is_admin() ) {
		if ( Options::getInstance()->getSeoMode() == OptionsInterface::SEO_MODE_SUBFOLDER ) {
			add_filter( 'do_parse_request', 'mcs_filter_uri' );
			add_filter( 'home_url', 'mcs_filter_home_url' );
		}

		ob_start( function ( $body ) {
			return Data::getInstance()->replaceTags( $body );
		} );
	}
}

function mcs_filter_home_url( $url ) {
	$siteUrl  = site_url();
	$options  = Options::getInstance();
	$location = Data::getInstance()->getCurrentLocation();
	if ( $location &&
		 ( $location->getId() != $options->getDefaultLocationId()
		   || $location->getType() != $options->getDefaultLocationType()
		 )
	) {
		return str_replace( $siteUrl, $siteUrl . '/' . $location->getSubDomain(), $url );
	}

	return $url;
}

function mcs_filter_uri() {
	Data::getInstance()->getCurrentLocation();

	return true;
}

/**
 * Pill field callback function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 */
function mcs_base_domain_cb() {
	$setting = get_option( 'mcs_base_domain' );
	?>
	<label>
		<input type="text" name="mcs_base_domain" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
	</label>
	<p class="description" id="tagline-description"><?php _e( 'Base domain of your site, f.e.: example.com' ); ?></p>
	<?php
}

function mcs_main_html() {
//	wp_enqueue_style( 'mcs-styles', plugin_dir_url( '' ) . 'mcs/admin/src/App.css' );

	?>
	<div class="wrap">
		<h1><?= esc_html( get_admin_page_title() ); ?></h1>
		<noscript>You need to enable JavaScript to run this app.</noscript>
		<div id="mcs-admin-root"></div>
	</div>
	<?php
}

function mcs_options_page_html() {
	// add error/update messages

	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'mcs_messages', 'mcs_message', __( 'Settings Saved', 'mcs' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'mcs_messages' );
	?>
	<div class="wrap">
		<h1><?= esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "mcs"
			settings_fields( 'mcs' );
			// output setting sections and their fields
			// (sections are registered for "mcs", each field is registered to a specific section)
			do_settings_sections( 'mcs' );
			// output save settings button
			submit_button( 'Save my Settings' );
			?>
		</form>
	</div>
	<?php
}

function mcs_register_routes() {
	( new CitiesController() )->register_routes();
	( new ProvincesController() )->register_routes();
	( new CountriesController() )->register_routes();
	( new CityFieldValuesController() )->register_routes();
	( new ProvinceFieldValuesController() )->register_routes();
	( new CountryFieldValuesController() )->register_routes();
	( new FieldsController() )->register_routes();
	( new FieldValuesController() )->register_routes();
	( new OptionsController() )->register_routes();
}
