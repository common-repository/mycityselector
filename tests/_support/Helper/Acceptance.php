<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Codeception\Util\Autoload;

Autoload::addNamespace( '', __DIR__ . '/../../' );

class Acceptance extends Module {

	const BASE_DOMAIN = 'wordpress.local';

	public function _beforeSuite( $settings = array() ) {
		$output = shell_exec( 'cd /var/www/html; wp db reset --allow-root --yes' );
		$output = shell_exec( 'cd /var/www/html; wp core install --url=' . self::BASE_DOMAIN
		                      . ' --title=MCS --admin_user=admin --admin_password=admin --admin_email=admin@example.org --allow-root' );
		$output = shell_exec( 'cd /var/www/html; wp theme activate twentynineteen --allow-root' );
		$output = shell_exec( 'cd /var/www/html; wp plugin activate mycityselector --allow-root' );
		$output = shell_exec( 'cd /var/www/html; wp widget add mcs_widget sidebar-1 --allow-root' );
		$output = shell_exec( 'cd /var/www/html; wp config set WP_ALLOW_MULTISITE true  --anchor="/** Custom" --allow-root' );

		require_once __DIR__ . '/../../../../../../wp-load.php';
	}

	public function _afterSuite() {

	}

	public function getFullUrl(): string {
		return $this->getModule( 'WebDriver' )->webDriver->getCurrentURL();
	}

	public function getHost() {
		return parse_url( $this->getFullUrl(), PHP_URL_HOST );
	}
}
