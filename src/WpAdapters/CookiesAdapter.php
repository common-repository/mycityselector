<?php

namespace Mcs\WpAdapters;

use Mcs\Interfaces\CookiesAdapterInterface;

class CookiesAdapter implements CookiesAdapterInterface {

	public function getInt( string $cookieName ): ?int {
		return key_exists( $cookieName, $_COOKIE ) ? (int) sanitize_key( $_COOKIE[ $cookieName ] ) : null;
	}

	public function getString( string $cookieName ): ?string {
		return key_exists( $cookieName, $_COOKIE ) ? sanitize_text_field( $_COOKIE[ $cookieName ] ) : null;
	}
}
