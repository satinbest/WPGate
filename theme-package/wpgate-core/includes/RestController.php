<?php
/**
 * WPGate Standalone Core - REST API Controller
 *
 * Exposes secure REST API endpoints under route `wpgate/v1` for the Vue 3 admin interface.
 *
 * @package WPGate\Core
 * @version 0.1.4
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class RestController {

	public const REST_NAMESPACE = 'wpgate/v1';

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		// GET & POST /wpgate/v1/settings
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ self::class, 'get_settings' ],
					'permission_callback' => [ self::class, 'permissions_check' ],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ self::class, 'save_settings' ],
					'permission_callback' => [ self::class, 'permissions_check' ],
				],
			]
		);

		// POST /wpgate/v1/settings/reset
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/reset',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ self::class, 'reset_settings' ],
				'permission_callback' => [ self::class, 'permissions_check' ],
			]
		);

		// GET /wpgate/v1/logs
		register_rest_route(
			self::REST_NAMESPACE,
			'/logs',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_logs' ],
				'permission_callback' => [ self::class, 'permissions_check' ],
			]
		);

		// POST /wpgate/v1/logs/clear
		register_rest_route(
			self::REST_NAMESPACE,
			'/logs/clear',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ self::class, 'clear_logs' ],
				'permission_callback' => [ self::class, 'permissions_check' ],
			]
		);

		// GET /wpgate/v1/presets
		register_rest_route(
			self::REST_NAMESPACE,
			'/presets',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_presets' ],
				'permission_callback' => [ self::class, 'permissions_check' ],
			]
		);

		// GET /wpgate/v1/stats
		register_rest_route(
			self::REST_NAMESPACE,
			'/stats',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_stats' ],
				'permission_callback' => [ self::class, 'permissions_check' ],
			]
		);
	}

	/**
	 * Permissions check: Only administrators with manage_options capability may access WPGate API.
	 *
	 * @return bool
	 */
	public static function permissions_check(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * GET /wpgate/v1/settings
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_settings(): \WP_REST_Response {
		$settings = Settings::get_all( true );

		$disabled_by_constant = ( defined( 'WPGATE_DISABLED' ) && WPGATE_DISABLED ) ||
		                         ( defined( 'WPGATE_CORE_DISABLED' ) && WPGATE_CORE_DISABLED );

		$system = [
			'version'              => defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.4',
			'loading_method'       => Context::get_loading_method(),
			'disabled_by_constant' => $disabled_by_constant,
			'php_version'          => PHP_VERSION,
			'wp_version'           => $GLOBALS['wp_version'] ?? 'unknown',
			'is_multisite'         => is_multisite(),
			'active_mode'          => $disabled_by_constant ? 'disabled' : ( $settings['mode'] ?? 'disabled' ),
			'author'               => 'Hossein Mohammadpour',
			'author_uri'           => 'https://hosseinmohammadpour.ir',
			'support_email'        => 'info@hosseinmohammadpour.ir',
		];

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => [
					'settings' => $settings,
					'system'   => $system,
				],
			],
			200
		);
	}

	/**
	 * POST /wpgate/v1/settings
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public static function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = $request->get_body_params();
		}

		if ( ! is_array( $params ) ) {
			return new \WP_REST_Response(
				[
					'success' => false,
					'message' => 'Invalid JSON body provided.',
				],
				400
			);
		}

		$sanitized = Settings::sanitize( $params );
		$saved     = Settings::save( $sanitized );

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => 'Settings saved successfully.',
				'data'    => [
					'settings' => Settings::get_all( true ),
				],
			],
			200
		);
	}

	/**
	 * POST /wpgate/v1/settings/reset
	 *
	 * @return \WP_REST_Response
	 */
	public static function reset_settings(): \WP_REST_Response {
		Settings::reset();

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => 'Settings have been reset to factory defaults.',
				'data'    => [
					'settings' => Settings::get_all( true ),
				],
			],
			200
		);
	}

	/**
	 * GET /wpgate/v1/logs
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public static function get_logs( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = max( 1, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 25 ) ) );
		$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$status   = sanitize_key( (string) $request->get_param( 'status' ) );

		$all_logs = Logger::get_logs();

		// Filter
		if ( '' !== $search || ( '' !== $status && 'all' !== $status ) ) {
			$all_logs = array_filter(
				$all_logs,
				function( $log ) use ( $search, $status ) {
					if ( ! is_array( $log ) ) {
						return false;
					}

					// Search filter
					if ( '' !== $search ) {
						$haystack = ( $log['url'] ?? '' ) . ' ' . ( $log['host'] ?? '' ) . ' ' . ( $log['mode'] ?? '' );
						if ( false === stripos( $haystack, $search ) ) {
							return false;
						}
					}

					// Status filter
					if ( '' !== $status && 'all' !== $status ) {
						$mode = $log['mode'] ?? '';
						if ( 'blocked' === $status && false === strpos( $mode, 'block' ) ) {
							return false;
						}
						if ( 'allowed' === $status && false === strpos( $mode, 'allow' ) && false === strpos( $mode, 'bypass' ) ) {
							return false;
						}
						if ( 'rewritten' === $status && false === strpos( $mode, 'rewritten' ) ) {
							return false;
						}
					}

					return true;
				}
			);
		}

		// Reverse chronological order
		$all_logs = array_reverse( array_values( $all_logs ) );
		$total    = count( $all_logs );
		$offset   = ( $page - 1 ) * $per_page;
		$paged    = array_slice( $all_logs, $offset, $per_page );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => [
					'items'       => $paged,
					'total'       => $total,
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => (int) ceil( $total / $per_page ),
				],
			],
			200
		);
	}

	/**
	 * POST /wpgate/v1/logs/clear
	 *
	 * @return \WP_REST_Response
	 */
	public static function clear_logs(): \WP_REST_Response {
		Logger::clear_logs();

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => 'Diagnostic logs cleared successfully.',
			],
			200
		);
	}

	/**
	 * GET /wpgate/v1/presets
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_presets(): \WP_REST_Response {
		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => Settings::get_presets(),
			],
			200
		);
	}

	/**
	 * GET /wpgate/v1/stats
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_stats(): \WP_REST_Response {
		$logs     = Logger::get_logs();
		$settings = Settings::get_all();

		$total     = count( $logs );
		$blocked   = 0;
		$allowed   = 0;
		$rewritten = 0;

		foreach ( $logs as $log ) {
			if ( ! is_array( $log ) ) {
				continue;
			}
			$mode = $log['mode'] ?? '';
			if ( false !== strpos( $mode, 'block' ) ) {
				$blocked++;
			} elseif ( false !== strpos( $mode, 'rewritten' ) ) {
				$rewritten++;
			} else {
				$allowed++;
			}
		}

		$whitelist_count = count( Settings::parse_host_list( $settings['whitelist'] ?? '' ) );
		$blacklist_count = count( Settings::parse_host_list( $settings['blacklist'] ?? '' ) );

		return new \WP_REST_Response(
			[
				'success' => true,
				'data'    => [
					'total_logs'      => $total,
					'blocked_count'   => $blocked,
					'allowed_count'   => $allowed,
					'rewritten_count' => $rewritten,
					'whitelist_count' => $whitelist_count,
					'blacklist_count' => $blacklist_count,
					'active_mode'     => $settings['mode'] ?? 'disabled',
					'logging_enabled' => ( $settings['enable_logging'] ?? 'no' ) === 'yes',
					'timeout_guard'   => ( $settings['enable_timeout_guard'] ?? 'no' ) === 'yes',
					'updates_blocked' => ( $settings['disable_updates'] ?? 'no' ) === 'yes',
				],
			],
			200
		);
	}
}
