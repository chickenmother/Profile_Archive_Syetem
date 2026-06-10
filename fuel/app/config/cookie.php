<?php
/**
 * Cookie configuration.
 * 
 * These settings are merged with Fuel's core Cookie class defaults.
 * 
 * 'expiration'  => 0 (session cookie), or seconds (e.g., 604800 = 7 days)
 * 'path'        => '/' (available throughout the entire domain)
 * 'domain'      => null (current domain only)
 * 'secure'      => false (set to true for HTTPS-only)
 * 'http_only'   => true (prevents JavaScript access to cookies)
 */
return array(
	'expiration'            => 604800,  // 7 days default expiration
	'path'                  => '/',
	'domain'                => null,
	'secure'                => false,
	'http_only'             => true,
);