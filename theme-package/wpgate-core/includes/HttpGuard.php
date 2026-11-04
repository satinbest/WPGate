<?php
/**
 * WPGate Standalone Core - HTTP Request Guard
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class HttpGuard {

	/**
	 * Intercept HTTP requests before they are sent.
	 *
	 * @param false|array|\WP_Error $preempt     Whether to preempt an HTTP request's return value.
	 * @param array                 $parsed_args HTTP request arguments.
	 * @param string                $url         The request URL.
	 * @return false|array|\WP_Error
	 */
	public static function filter( $preempt, $parsed_args, $url ) {
		// If already handled/preempted by another hook, return it
		if ( false !== $preempt && null !== $preempt ) {
			return $preempt;
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $host ) {
			return $preempt;
		}

		$host = strtolower( trim( $host ) );

		// Local requests (loopback, localhost, site host) are always exempt
		if ( self::is_local_host( $host ) ) {
			return $preempt;
		}

		$settings = Settings::get_all();

		// Check whitelisted update rule
		if ( self::should_block_non_whitelisted_update( $url, $host, $parsed_args, $settings ) ) {
			Logger::log( $url, $host, 'blocked_non_whitelisted_update_server' );
			return new \WP_Error( 'wpgate_blocked', 'Blocked by WPGate Whitelisted Update Rule' );
		}

		// Check Mixpanel rule
		if ( 'yes' === ( $settings['block_mixpanel'] ?? 'no' ) && self::is_mixpanel_host( $host ) ) {
			Logger::log( $url, $host, 'blocked_mixpanel' );
			return new \WP_Error( 'wpgate_blocked', 'Blocked by WPGate Mixpanel Rule' );
		}

		$mode = $settings['mode'] ?? 'disabled';

		if ( 'disabled' === $mode ) {
			return $preempt;
		}

		if ( 'whitelist' === $mode ) {
			$whitelist = Settings::parse_host_list( $settings['whitelist'] ?? '' );
			if ( ! self::host_in_list( $host, $whitelist ) ) {
				Logger::log( $url, $host, 'blocked_by_whitelist_mode' );
				return new \WP_Error( 'wpgate_blocked', 'Blocked by WPGate Whitelist Mode' );
			}
		} elseif ( 'blacklist' === $mode ) {
			$blacklist = Settings::parse_host_list( $settings['blacklist'] ?? '' );
			if ( self::host_in_list( $host, $blacklist ) ) {
				Logger::log( $url, $host, 'blocked_by_blacklist_mode' );
				return new \WP_Error( 'wpgate_blocked', 'Blocked by WPGate Blacklist Mode' );
			}
		}

		return $preempt;
	}

	/**
	 * Check if host is in the given list of allowed/blocked domains.
	 * Supports exact matching and subdomain matching.
	 *
	 * @param string $host
	 * @param array  $domains
	 * @return bool
	 */
	public static function host_in_list( string $host, array $domains ): bool {
		$host = strtolower( trim( $host ) );
		if ( '' === $host || empty( $domains ) ) {
			return false;
		}

		// Strip trailing dot if present (FQDN)
		$host = rtrim( $host, '.' );

		$normalized_host = self::normalize_host( $host );

		foreach ( $domains as $domain ) {
			$domain = strtolower( trim( (string) $domain ) );
			if ( '' === $domain ) {
				continue;
			}

			// Clean domain if passed with scheme or trailing slash
			$parsed = wp_parse_url( $domain, PHP_URL_HOST );
			if ( $parsed ) {
				$domain = $parsed;
			} else {
				$clean  = preg_replace( '#^https?://#i', '', $domain );
				$domain = explode( '/', $clean, 2 )[0];
			}
			$domain = preg_replace( '/:\d+$/', '', $domain );
			$domain = rtrim( $domain, '.' );

			if ( '' === $domain ) {
				continue;
			}

			// 1. Exact match
			if ( $host === $domain ) {
				return true;
			}

			// 2. Subdomain match (e.g. sub.example.com matches example.com)
			$suffix = '.' . $domain;
			if ( strlen( $host ) > strlen( $suffix ) && substr( $host, -strlen( $suffix ) ) === $suffix ) {
				return true;
			}

			// 3. WWW normalized match
			$norm_domain = self::normalize_host( $domain );
			if ( $normalized_host === $norm_domain ) {
				return true;
			}

			$norm_suffix = '.' . $norm_domain;
			if ( strlen( $normalized_host ) > strlen( $norm_suffix ) && substr( $normalized_host, -strlen( $norm_suffix ) ) === $norm_suffix ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Strip leading www. from host name.
	 *
	 * @param string $host
	 * @return string
	 */
	public static function normalize_host( string $host ): string {
		$host = strtolower( trim( $host ) );
		if ( 0 === strpos( $host, 'www.' ) ) {
			return substr( $host, 4 );
		}
		return $host;
	}

	/**
	 * Check if host is a local/internal address.
	 *
	 * @param string $host
	 * @return bool
	 */
	public static function is_local_host( string $host ): bool {
		$host = strtolower( trim( $host ) );
		if ( '' === $host ) {
			return true;
		}

		if ( in_array( $host, [ 'localhost', '127.0.0.1', '::1', '0.0.0.0' ], true ) ) {
			return true;
		}

		// Check site URL host
		if ( function_exists( 'home_url' ) ) {
			$site_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
			if ( '' !== $site_host && self::normalize_host( $host ) === self::normalize_host( $site_host ) ) {
				return true;
			}
		}

		if ( function_exists( 'site_url' ) ) {
			$wp_host = (string) wp_parse_url( site_url(), PHP_URL_HOST );
			if ( '' !== $wp_host && self::normalize_host( $host ) === self::normalize_host( $wp_host ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if host belongs to Mixpanel.
	 *
	 * @param string $host
	 * @return bool
	 */
	public static function is_mixpanel_host( string $host ): bool {
		$mixpanel_hosts = [
			'api-eu.mixpanel.com',
			'api.mixpanel.com',
			'cdn.mxpnl.com',
			'api-js.mixpanel.com',
		];

		return in_array( $host, $mixpanel_hosts, true );
	}

	/**
	 * Check if request is an update check that should be blocked because its server is not in the whitelist.
	 *
	 * @param string $url
	 * @param string $host
	 * @param array  $parsed_args
	 * @param array  $settings
	 * @return bool
	 */
	private static function should_block_non_whitelisted_update( string $url, string $host, $parsed_args, array $settings ): bool {
		if ( 'yes' !== ( $settings['disable_updates'] ?? 'no' ) ) {
			return false;
		}

		if ( 'yes' !== ( $settings['allow_whitelisted_updates'] ?? 'yes' ) ) {
			return false;
		}

		if ( ! self::is_plugin_or_theme_update_request( $url, $parsed_args ) ) {
			return false;
		}

		if ( self::is_local_host( $host ) ) {
			return false;
		}

		$whitelist = Settings::parse_host_list( $settings['whitelist'] ?? '' );
		return ! self::host_in_list( $host, $whitelist );
	}

	/**
	 * Determine if request is targeting a theme or plugin update endpoint.
	 *
	 * @param string $url
	 * @param mixed  $parsed_args
	 * @return bool
	 */
	public static function is_plugin_or_theme_update_request( string $url, $parsed_args ): bool {
		if ( is_array( $parsed_args ) ) {
			$body = isset( $parsed_args['body'] ) && is_array( $parsed_args['body'] ) ? $parsed_args['body'] : [];
			if ( isset( $body['plugins'] ) || isset( $body['themes'] ) ) {
				return true;
			}
		}

		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( '' === $path ) {
			return false;
		}

		return false !== strpos( $path, '/plugins/update-check/' ) || false !== strpos( $path, '/themes/update-check/' );
	}
}
