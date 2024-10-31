<?php


namespace Mcs\WpModels;


use Mcs\Interfaces\DataInterface;
use WP_Widget;

class McsWidget extends WP_Widget {
	protected $modes = [
		DataInterface::LIST_MODE_CITIES                     => 'Cities only',
		DataInterface::LIST_MODE_PROVINCES_CITIES           => 'Provinces / States and Cities',
		DataInterface::LIST_MODE_COUNTRIES_PROVINCES_CITIES => 'Countries,  Provinces / States and Cities',
		DataInterface::LIST_MODE_COUNTRIES_CITIES           => 'Countries and Cities'
	];


	public function __construct() {
		$widget_ops = array(
			'classname'   => 'mcs-widget',
			'description' => 'MyCitySelector - location selector widget',
		);
		parent::__construct( 'mcs_widget', 'MyCitySelector Widget', $widget_ops );
	}

	public function widget( $args, $instance ) {
		$options         = Options::getInstance();
		$defaultLocation = $options->getDefaultLocation();
		wp_add_inline_script( 'mcs-widget-script', '
		window.mcs={};
		window.mcs.options={};
		window.mcs.options.title=\'' . $instance['title'] . '\';
		window.mcs.options.list_mode=' . $instance['list_mode'] . ';
		window.mcs.options.seo_mode=' . $options->getSeoMode() . ';
		window.mcs.options.default_location_id=' . ( $defaultLocation ? $defaultLocation->getId() : 'null' ) . ';
		window.mcs.options.default_location_type=' . ( $defaultLocation ? $defaultLocation->getType() : 'null' ) . ';
		window.mcs.options.base_domain="' . $options->getBaseDomain() . '";
		window.mcs.data=JSON.parse(' . json_encode( Data::getInstance()->getWidgetDataJson() ) . ');
		', 'before' );
		?>
		<div id="mcs-widget"></div>
		<?php
	}

	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			[
				'title'     => 'Please select your location',
				'list_mode' => 0,
			]
		);
		?>
		<p>
			<label for="<?= esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?= esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?= esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				   value="<?php echo esc_attr( $instance['title'] ); ?>"/>
		</p>
		<p>
			<label for="<?= esc_attr( $this->get_field_id( 'list_mode' ) ); ?>"><?php _e( 'Mode:' ); ?></label>
			<select class="widefat" id="<?= esc_attr( $this->get_field_id( 'list_mode' ) ); ?>"
					name="<?= esc_attr( $this->get_field_name( 'list_mode' ) ); ?>">
				<?php foreach ( $this->modes as $modeId => $modeTitle ) : ?>
					<option
						value="<?= esc_attr( $modeId ); ?>" <?php selected( $modeId, $instance['list_mode'] ); ?>>
						<?= esc_html( $modeTitle ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance              = [];
		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['list_mode'] = (int) sanitize_text_field($new_instance['list_mode']);

		return $instance;
	}
}
