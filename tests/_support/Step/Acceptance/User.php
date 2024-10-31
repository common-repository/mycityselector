<?php

namespace Step\Acceptance;

use AcceptanceTester;
use Exception;
use Helper\Acceptance;
use Mcs\Interfaces\DataInterface;
use Mcs\Interfaces\OptionsInterface;
use Mcs\WpModels\McsWidget;
use Mcs\WpModels\Options;
use WP_Post;

class User extends AcceptanceTester {

	/**
	 * @throws Exception
	 */
	public function configurePlugin() {
//		$options = $this->getOptions();
//		$options->setBaseDomain( Acceptance::BASE_DOMAIN );
	}

	public function getPost(): WP_Post {
		return get_post( 1 );
	}

	public function getOptions(): OptionsInterface {
		return Options::getInstance();
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return bool|int
	 */
	public function updatePost( WP_Post $post ) {
		return wp_update_post( $post );
	}

	public function getWidget(): McsWidget {
		global $wp_widget_factory;

		return $wp_widget_factory->get_widget_object( 'mcs_widget' );
	}

	public function updateWidgetOptions( string $title = null, int $listMode = DataInterface::LIST_MODE_CITIES ) {
		$widget                        = $this->getWidget();
		$widgetOptions                 = get_option( 'widget_' . $widget->id_base, [] );
		if ($title) {
			$widgetOptions[1]['title']     = $title;
		}
		$widgetOptions[1]['list_mode'] = $listMode;
		$widget->save_settings( $widgetOptions );
	}
}
