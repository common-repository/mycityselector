<?php

if (!defined('MCS_PREFIX')) {
	define( 'MCS_PREFIX', 'mcs_' );
}

return
	[
		'paths'         => [
			'migrations' => '%%PHINX_CONFIG_DIR%%/src/db/migrations',
			'seeds'      => '%%PHINX_CONFIG_DIR%%/src/db/seeds'
		],
		'environments'  => [
			'default_migration_table' => 'mcs_phinxlog',
			'default_environment'     => 'production',
			'production'              => [
				'adapter' => 'mysql',
				'host'    => DB_HOST,
				'name'    => DB_NAME,
				'user'    => DB_USER,
				'pass'    => DB_PASSWORD,
				'charset' => DB_CHARSET,
			],
		],
		'version_order' => 'creation'
	];
