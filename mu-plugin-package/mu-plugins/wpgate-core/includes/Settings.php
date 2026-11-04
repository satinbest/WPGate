<?php
/**
 * WPGate Standalone Core - Settings Management
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class Settings {

	public const OPTION_KEY = 'wpgate_http_control_settings';

	/**
	 * In-memory settings cache.
	 *
	 * @var array|null
	 */
	private static $cached_settings = null;

	/**
	 * Get all settings merged with defaults.
	 *
	 * @param bool $force_refresh
	 * @return array
	 */
	public static function get_all( bool $force_refresh = false ): array {
		if ( null !== self::$cached_settings && ! $force_refresh ) {
			return self::$cached_settings;
		}

		$defaults = self::get_defaults();
		$saved    = get_option( self::OPTION_KEY, [] );

		if ( ! is_array( $saved ) ) {
			$saved = [];
		}

		self::$cached_settings = wp_parse_args( $saved, $defaults );

		// Resolve default gravatar URL dynamically if not specified
		if ( empty( self::$cached_settings['gravatar_url'] ) ) {
			self::$cached_settings['gravatar_url'] = Context::get_asset_url( 'user.jpg' );
		}

		return self::$cached_settings;
	}

	/**
	 * Save sanitized settings array.
	 *
	 * @param array $settings
	 * @return bool
	 */
	public static function save( array $settings ): bool {
		self::$cached_settings = null;
		return (bool) update_option( self::OPTION_KEY, $settings, false );
	}

	/**
	 * Reset all settings to default values.
	 *
	 * @return bool
	 */
	public static function reset(): bool {
		self::$cached_settings = null;
		return (bool) delete_option( self::OPTION_KEY );
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Load default settings from config.
	 *
	 * @return array
	 */
	public static function get_defaults(): array {
		$file = Context::get_base_dir() . '/config/defaults.php';
		if ( file_exists( $file ) ) {
			$defaults = require $file;
			if ( is_array( $defaults ) ) {
				return $defaults;
			}
		}

		return [
			'mode'                     => 'disabled',
			'whitelist'                => 'api.zarinpal.com',
			'blacklist'                => '',
			'disable_telemetry'        => 'yes',
			'disable_updates'          => 'no',
			'allow_whitelisted_updates' => 'yes',
			'disable_gravatar'         => 'yes',
			'gravatar_url'             => '',
			'enable_logging'           => 'no',
			'log_retention_days'       => '7',
			'log_max_entries'          => '300',
			'log_asset_events'         => 'no',
			'local_asset_rewrite'      => 'yes',
			'block_mixpanel'           => 'yes',
			'strict_asset_block'       => 'yes',
			'enable_front_vazirmatn'   => 'no',
			'enable_timeout_guard'     => 'no',
			'max_request_timeout'      => '3',
		];
	}

	/**
	 * Load domain presets from config.
	 *
	 * @return array
	 */
	public static function get_presets(): array {
		$file = Context::get_base_dir() . '/config/domain-presets.php';
		if ( file_exists( $file ) ) {
			$presets = require $file;
			if ( is_array( $presets ) ) {
				$all_domains = [];
				foreach ( $presets as &$preset ) {
					if ( 'all' !== ( $preset['key'] ?? '' ) && ! empty( $preset['domains'] ) && is_array( $preset['domains'] ) ) {
						$all_domains = array_merge( $all_domains, $preset['domains'] );
					}
				}
				unset( $preset );

				foreach ( $presets as &$preset ) {
					if ( 'all' === ( $preset['key'] ?? '' ) ) {
						$preset['domains'] = array_values( array_unique( $all_domains ) );
					}
				}
				unset( $preset );

				return $presets;
			}
		}

		return [];
	}

	/**
	 * Sanitize raw settings input from admin post.
	 *
	 * @param mixed $input
	 * @return array
	 */
	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : [];

		$mode = isset( $input['mode'] ) ? sanitize_text_field( $input['mode'] ) : 'disabled';
		if ( ! in_array( $mode, [ 'disabled', 'whitelist', 'blacklist' ], true ) ) {
			$mode = 'disabled';
		}

		$gravatar_url = isset( $input['gravatar_url'] ) ? esc_url_raw( trim( (string) $input['gravatar_url'] ) ) : '';
		if ( '' === $gravatar_url ) {
			$gravatar_url = Context::get_asset_url( 'user.jpg' );
		}

		$retention_days = isset( $input['log_retention_days'] ) ? (string) intval( $input['log_retention_days'] ) : '7';
		if ( ! in_array( $retention_days, [ '1', '3', '7', '15', '30' ], true ) ) {
			$retention_days = '7';
		}

		$log_max_entries = isset( $input['log_max_entries'] ) ? (string) self::normalize_log_limit( $input['log_max_entries'] ) : '300';
		$max_request_timeout = isset( $input['max_request_timeout'] ) ? (string) self::normalize_timeout_limit( $input['max_request_timeout'] ) : '3';

		$sanitized = [
			'mode'                     => $mode,
			'whitelist'                => self::sanitize_host_list( $input['whitelist'] ?? '' ),
			'blacklist'                => self::sanitize_host_list( $input['blacklist'] ?? '' ),
			'disable_telemetry'        => ( $input['disable_telemetry'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'disable_updates'          => ( $input['disable_updates'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'allow_whitelisted_updates' => ( $input['allow_whitelisted_updates'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'disable_gravatar'         => ( $input['disable_gravatar'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'gravatar_url'             => $gravatar_url,
			'enable_logging'           => ( $input['enable_logging'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'log_retention_days'       => $retention_days,
			'log_max_entries'          => $log_max_entries,
			'log_asset_events'         => ( $input['log_asset_events'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'local_asset_rewrite'      => ( $input['local_asset_rewrite'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'block_mixpanel'           => ( $input['block_mixpanel'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'strict_asset_block'       => ( $input['strict_asset_block'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'enable_front_vazirmatn'   => ( $input['enable_front_vazirmatn'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'enable_timeout_guard'     => ( $input['enable_timeout_guard'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
			'max_request_timeout'      => $max_request_timeout,
		];

		self::$cached_settings = $sanitized;

		return $sanitized;
	}

	/**
	 * Normalize multiline host string to clean array or newline-delimited string.
	 *
	 * @param string $raw_hosts
	 * @return string
	 */
	public static function sanitize_host_list( $raw_hosts ): string {
		$raw_hosts = is_string( $raw_hosts ) ? $raw_hosts : '';
		$lines     = preg_split( '/\r\n|\r|\n/', $raw_hosts );
		$hosts     = [];

		foreach ( $lines as $line ) {
			$line = trim( (string) $line );
			if ( '' === $line ) {
				continue;
			}

			$host = wp_parse_url( $line, PHP_URL_HOST );
			if ( ! $host ) {
				$clean = preg_replace( '#^https?://#i', '', $line );
				$clean = explode( '/', $clean, 2 )[0];
				$host  = $clean;
			}

			$host = strtolower( trim( (string) $host ) );
			$host = preg_replace( '/:\d+$/', '', $host );

			if ( '' !== $host && false === strpos( $host, ' ' ) ) {
				$hosts[] = $host;
			}
		}

		$hosts = array_values( array_unique( $hosts ) );
		return implode( "\n", $hosts );
	}

	/**
	 * Parse newline string into array of trimmed lowercase domains.
	 *
	 * @param string $multiline_hosts
	 * @return array
	 */
	public static function parse_host_list( string $multiline_hosts ): array {
		if ( '' === trim( $multiline_hosts ) ) {
			return [];
		}

		$lines   = preg_split( '/\r\n|\r|\n/', $multiline_hosts );
		$domains = [];

		foreach ( $lines as $line ) {
			$domain = strtolower( trim( (string) $line ) );
			if ( '' !== $domain ) {
				$domains[] = $domain;
			}
		}

		return array_values( array_unique( $domains ) );
	}

	/**
	 * Clamp timeout limit between 1 and 30 seconds.
	 *
	 * @param mixed $raw_timeout
	 * @return int
	 */
	public static function normalize_timeout_limit( $raw_timeout ): int {
		$timeout = (int) $raw_timeout;
		if ( $timeout < 1 ) {
			return 1;
		}
		if ( $timeout > 30 ) {
			return 30;
		}
		return $timeout;
	}

	/**
	 * Clamp log limit between 50 and 2000 entries.
	 *
	 * @param mixed $raw_limit
	 * @return int
	 */
	public static function normalize_log_limit( $raw_limit ): int {
		$limit = (int) $raw_limit;
		if ( $limit < 50 ) {
			return 50;
		}
		if ( $limit > 2000 ) {
			return 2000;
		}
		return $limit;
	}
}
