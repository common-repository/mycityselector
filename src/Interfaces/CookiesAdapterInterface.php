<?php

namespace Mcs\Interfaces;

interface CookiesAdapterInterface {
	public function getInt(string $cookieName): ?int;

	public function getString(string $cookieName): ?string;
}
