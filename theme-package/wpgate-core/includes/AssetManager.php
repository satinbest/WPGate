<?php
/**
 * WPGate Standalone Core - Asset Replacement Manager
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class AssetManager {

	/**
	 * Known public CDN hostnames.
	 *
	 * @var array
	 */
	private const CDN_HOSTS = [
		'ajax.googleapis.com',
		'cdnjs.cloudflare.com',
		'cdn.jsdelivr.net',
		'unpkg.com',
		'code.jquery.com',
		'fonts.googleapis.com',
		'fonts.gstatic.com',
		'use.fontawesome.com',
		'kit.fontawesome.com',
		'maxcdn.bootstrapcdn.com',
	];

	/**
	 * Check if current request is inside the WordPress Customizer.
	 *
	 * @return bool
	 */
	public static function is_customizer_context(): bool {
		global $pagenow;

		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			return true;
		}

		if ( is_admin() && 'customize.php' === $pagenow ) {
			return true;
		}

		return isset( $_REQUEST['customize_changeset_uuid'] ) ||
			isset( $_REQUEST['customize_theme'] ) ||
			isset( $_REQUEST['customize_messenger_channel'] );
	}

	/**
	 * Filter script src URLs for CDN replacement and blocking.
	 *
	 * @param string $src
	 * @param string $handle
	 * @return string
	 */
	public static function replace_script_src( string $src, string $handle ): string {
		$settings = Settings::get_all();

		if ( ( $settings['local_asset_rewrite'] ?? 'no' ) !== 'yes' ) {
			return $src;
		}

		if ( self::is_customizer_context() ) {
			return $src;
		}

		$host = (string) wp_parse_url( $src, PHP_URL_HOST );
		$path = (string) wp_parse_url( $src, PHP_URL_PATH );

		if ( '' === $host || '' === $path ) {
			return $src;
		}

		$host = strtolower( $host );

		// Local / same-site assets are passed through untouched
		if ( self::is_same_site_host( $host ) ) {
			Logger::log( $src, $host, 'local_asset_bypassed_same_host_script' );
			return $src;
		}

		// Blacklisted asset hosts
		if ( self::is_blacklisted_host( $host, $settings ) ) {
			Logger::log( $src, $host, 'blocked_by_blacklist_asset_script' );
			return Context::get_asset_url( 'js/blocked-asset.js' );
		}

		// Mixpanel stub replacement
		if ( 'yes' === ( $settings['block_mixpanel'] ?? 'no' ) && HttpGuard::is_mixpanel_host( $host ) ) {
			Logger::log( $src, $host, 'mixpanel_rewritten_to_local_stub' );
			return Context::get_asset_url( 'js/mixpanel-stub.js' );
		}

		// Map known scripts to local equivalents
		$replacement = self::map_script_to_local( $path, $src );
		if ( '' !== $replacement ) {
			Logger::log( $src, $host, 'cdn_rewritten_to_local' );
			return $replacement;
		}

		// Strict asset block for unmapped external CDN assets
		if ( self::should_block_external_assets( $host, $settings ) ) {
			Logger::log( $src, $host, 'blocked_external_script_no_local' );
			return Context::get_asset_url( 'js/blocked-asset.js' );
		}

		return $src;
	}

	/**
	 * Filter stylesheet src URLs for CDN replacement and blocking.
	 *
	 * @param string $src
	 * @param string $handle
	 * @return string
	 */
	public static function replace_style_src( string $src, string $handle ): string {
		$settings = Settings::get_all();

		if ( ( $settings['local_asset_rewrite'] ?? 'no' ) !== 'yes' ) {
			return $src;
		}

		if ( self::is_customizer_context() ) {
			return $src;
		}

		$host = (string) wp_parse_url( $src, PHP_URL_HOST );
		$path = (string) wp_parse_url( $src, PHP_URL_PATH );

		if ( '' === $host || '' === $path ) {
			return $src;
		}

		$host = strtolower( $host );
		$path = strtolower( $path );

		if ( self::is_same_site_host( $host ) ) {
			Logger::log( $src, $host, 'local_asset_bypassed_same_host_style' );
			return $src;
		}

		if ( self::is_blacklisted_host( $host, $settings ) ) {
			Logger::log( $src, $host, 'blocked_by_blacklist_asset_style' );
			return Context::get_asset_url( 'css/blocked-asset.css' );
		}

		$replacement = self::map_style_to_local( $path, $src, $host );
		if ( '' !== $replacement ) {
			return $replacement;
		}

		if ( self::should_block_external_assets( $host, $settings ) ) {
			Logger::log( $src, $host, 'blocked_external_style_no_local' );
			return Context::get_asset_url( 'css/blocked-asset.css' );
		}

		return $src;
	}

	/**
	 * Enqueue local Vazirmatn font in front-end if enabled.
	 *
	 * @return void
	 */
	public static function maybe_enqueue_vazirmatn(): void {
		if ( is_admin() ) {
			return;
		}

		$settings = Settings::get_all();
		if ( ( $settings['enable_front_vazirmatn'] ?? 'no' ) !== 'yes' ) {
			return;
		}

		$css_path = Context::get_asset_path( 'vendor/google-fonts/vazirmatn.css' );
		if ( ! file_exists( $css_path ) ) {
			return;
		}

		wp_enqueue_style(
			'wpgate-vazirmatn',
			Context::get_asset_url( 'vendor/google-fonts/vazirmatn.css' ),
			[],
			WPGATE_CORE_VERSION
		);
	}

	/**
	 * Map remote script path to local equivalent.
	 *
	 * @param string $path
	 * @param string $original_src
	 * @return string
	 */
	private static function map_script_to_local( string $path, string $original_src ): string {
		$path = strtolower( $path );

		// Ace editor
		if ( preg_match( '#/ace(-min)?\.js$#', $path ) ) {
			$local = Context::get_asset_path( 'vendor/ace-builds/src-min-noconflict/ace.min.js' );
			if ( file_exists( $local ) ) {
				return Context::get_asset_url( 'vendor/ace-builds/src-min-noconflict/ace.min.js' );
			}
		}

		if ( preg_match( '#/ext-language_tools\.js$#', $path ) ) {
			$local = Context::get_asset_path( 'vendor/ace-builds/src-min-noconflict/ext-language_tools.js' );
			if ( file_exists( $local ) ) {
				return Context::get_asset_url( 'vendor/ace-builds/src-min-noconflict/ext-language_tools.js' );
			}
		}

		// Swiper JS
		if ( preg_match( '#/swiper(-bundle)?(\.min)?\.js$#', $path ) ) {
			$local = Context::get_asset_path( 'vendor/swiper/swiper-bundle.min.js' );
			if ( file_exists( $local ) ) {
				return Context::get_asset_url( 'vendor/swiper/swiper-bundle.min.js' );
			}
		}

		// WordPress Core JS libraries (prefer core includes_url if available, fallback to bundled)
		if ( preg_match( '#/jquery(\.min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/jquery/jquery.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/jquery.min.js' );
		}

		if ( preg_match( '#/jquery-migrate(\.min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/jquery/jquery-migrate.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/jquery-migrate.min.js' );
		}

		if ( preg_match( '#/underscore(-min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/underscore.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/underscore.min.js' );
		}

		if ( preg_match( '#/backbone(-min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/backbone.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/backbone.min.js' );
		}

		if ( preg_match( '#/react(\.production\.min|\.min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/dist/vendor/react.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/react.min.js' );
		}

		if ( preg_match( '#/react-dom(\.production\.min|\.min)?\.js$#', $path ) ) {
			return function_exists( 'includes_url' ) ? includes_url( 'js/dist/vendor/react-dom.min.js' ) : Context::get_asset_url( 'vendor/wp-core-js/react-dom.min.js' );
		}

		return apply_filters( 'wpgate_local_script_rewrite', '', $path, $original_src );
	}

	/**
	 * Map remote stylesheet path to local equivalent.
	 *
	 * @param string $path
	 * @param string $original_src
	 * @param string $host
	 * @return string
	 */
	private static function map_style_to_local( string $path, string $original_src, string $host ): string {
		// FontAwesome
		if ( false !== strpos( $path, 'fontawesome' ) || false !== strpos( $path, 'font-awesome' ) ) {
			if ( false !== strpos( $path, 'all.min.css' ) ) {
				$local = Context::get_asset_path( 'vendor/fontawesome/css/all.min.css' );
				if ( file_exists( $local ) ) {
					Logger::log( $original_src, $host, 'fontawesome_all_rewritten_to_local' );
					return Context::get_asset_url( 'vendor/fontawesome/css/all.min.css' );
				}
			}

			if ( false !== strpos( $path, 'v4-shims.min.css' ) ) {
				$local = Context::get_asset_path( 'vendor/fontawesome/css/v4-shims.min.css' );
				if ( file_exists( $local ) ) {
					Logger::log( $original_src, $host, 'fontawesome_shims_rewritten_to_local' );
					return Context::get_asset_url( 'vendor/fontawesome/css/v4-shims.min.css' );
				}
			}
		}

		// Google Fonts
		if ( 'fonts.googleapis.com' === $host && 0 === strpos( $path, '/css' ) ) {
			$families = self::extract_google_font_families( $original_src );

			if ( in_array( 'vazirmatn', $families, true ) ) {
				$local = Context::get_asset_path( 'vendor/google-fonts/vazirmatn.css' );
				if ( file_exists( $local ) ) {
					Logger::log( $original_src, $host, 'google_fonts_vazirmatn_rewritten_to_local' );
					return Context::get_asset_url( 'vendor/google-fonts/vazirmatn.css' );
				}
			}

			if ( in_array( 'roboto', $families, true ) || empty( $families ) ) {
				$local = Context::get_asset_path( 'vendor/google-fonts/roboto.css' );
				if ( file_exists( $local ) ) {
					Logger::log( $original_src, $host, 'google_fonts_rewritten_to_local' );
					return Context::get_asset_url( 'vendor/google-fonts/roboto.css' );
				}
			}
		}

		// Swiper CSS
		if ( preg_match( '#/swiper(-bundle)?(\.min)?\.css$#', $path ) ) {
			$local = Context::get_asset_path( 'vendor/swiper/swiper-bundle.min.css' );
			if ( file_exists( $local ) ) {
				Logger::log( $original_src, $host, 'swiper_rewritten_to_local' );
				return Context::get_asset_url( 'vendor/swiper/swiper-bundle.min.css' );
			}
		}

		// Dashicons
		if ( false !== strpos( $path, 'dashicons' ) ) {
			Logger::log( $original_src, $host, 'dashicons_rewritten_to_local' );
			return function_exists( 'includes_url' ) ? includes_url( 'css/dashicons.min.css' ) : Context::get_asset_url( 'vendor/dashicons/css/dashicons.min.css' );
		}

		// Elementor icons (eicons)
		if ( false !== strpos( $path, 'eicons' ) || false !== strpos( $path, 'elementor-icons' ) ) {
			$local = Context::get_asset_path( 'vendor/eicons/css/elementor-icons.min.css' );
			if ( file_exists( $local ) ) {
				Logger::log( $original_src, $host, 'eicons_rewritten_to_local' );
				return Context::get_asset_url( 'vendor/eicons/css/elementor-icons.min.css' );
			}
		}

		return apply_filters( 'wpgate_local_style_rewrite', '', $path, $original_src );
	}

	/**
	 * Extract font family names from Google Fonts CSS URL.
	 *
	 * @param string $src
	 * @return array
	 */
	public static function extract_google_font_families( string $src ): array {
		$query = (string) wp_parse_url( $src, PHP_URL_QUERY );
		if ( '' === $query ) {
			return [];
		}

		$families = [];
		$pairs    = explode( '&', $query );

		foreach ( $pairs as $pair ) {
			$pair = trim( $pair );
			if ( '' === $pair ) {
				continue;
			}

			$key_value = explode( '=', $pair, 2 );
			$key       = isset( $key_value[0] ) ? urldecode( $key_value[0] ) : '';
			$value     = isset( $key_value[1] ) ? urldecode( $key_value[1] ) : '';

			if ( 'family' !== strtolower( $key ) || '' === $value ) {
				continue;
			}

			$family_parts = explode( '|', $value );
			foreach ( $family_parts as $family_part ) {
				$family_part = trim( $family_part );
				if ( '' === $family_part ) {
					continue;
				}

				$family_name = explode( ':', $family_part, 2 )[0];
				$family_name = strtolower( trim( str_replace( '+', ' ', $family_name ) ) );
				if ( '' !== $family_name ) {
					$families[] = $family_name;
				}
			}
		}

		return array_values( array_unique( $families ) );
	}

	/**
	 * Check if host is the same as the current site's host.
	 *
	 * @param string $host
	 * @return bool
	 */
	private static function is_same_site_host( string $host ): bool {
		if ( '' === $host ) {
			return false;
		}

		if ( function_exists( 'home_url' ) ) {
			$site_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
			if ( '' !== $site_host ) {
				return HttpGuard::normalize_host( $site_host ) === HttpGuard::normalize_host( $host );
			}
		}

		return false;
	}

	/**
	 * Check if host is blacklisted in settings.
	 *
	 * @param string $host
	 * @param array  $settings
	 * @return bool
	 */
	private static function is_blacklisted_host( string $host, array $settings ): bool {
		if ( ( $settings['mode'] ?? 'disabled' ) !== 'blacklist' ) {
			return false;
		}

		$blacklist = Settings::parse_host_list( $settings['blacklist'] ?? '' );
		return HttpGuard::host_in_list( $host, $blacklist );
	}

	/**
	 * Check if an external asset should be strictly blocked.
	 *
	 * @param string $host
	 * @param array  $settings
	 * @return bool
	 */
	private static function should_block_external_assets( string $host, array $settings ): bool {
		if ( ( $settings['strict_asset_block'] ?? 'no' ) !== 'yes' ) {
			return false;
		}

		$is_known_cdn = in_array( $host, self::CDN_HOSTS, true );
		$is_mixpanel  = HttpGuard::is_mixpanel_host( $host );

		if ( ! $is_known_cdn && ! $is_mixpanel ) {
			return false;
		}

		// Whitelist check
		if ( ( $settings['mode'] ?? 'disabled' ) === 'whitelist' ) {
			$whitelist = Settings::parse_host_list( $settings['whitelist'] ?? '' );
			if ( HttpGuard::host_in_list( $host, $whitelist ) ) {
				return false;
			}
		}

		return ! self::is_same_site_host( $host );
	}
}
