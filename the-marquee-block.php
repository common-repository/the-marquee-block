<?php
/**
 * Plugin Name:       The Marquee Block
 * Description:       Display a scrolling piece of text horizontally on your website.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           1.0.3
 * Author:            Themes Kingdom
 * Author URI:        https://themeskingdom.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       the-marquee-block
 * Domain Path: /languages/
 *
 * @package create-block/the-marquee-block
 */

if ( ! defined( 'TMB_VERSION' ) ) {
	define( 'TMB_VERSION', '1.0.0' );
}

if ( ! defined( 'TMB_DIR_PATH' ) ) {
	define( 'TMB_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'TMB_DIR_URL' ) ) {
	define( 'TMB_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Gutenberg fallback notice.
 */
function gutenberg_missing_notice() {

	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'The Marquee Block plugin requires Gutenberg to be installed and active. You can download %s here.', 'the-marquee-block' ), '<a href="https://wordpress.org/plugins/gutenberg/" target="_blank">Gutenberg</a>' ) . '</strong></p></div>';
	
}

/**
 * Enqueue block editor JavaScript and CSS
 */
function tmb_scripts() {

	$tmb_block_path = '/assets/js/common.shared.js';

	wp_enqueue_script( 'jquery' );

	wp_enqueue_script(
		'tmb-blocks-frontend-js',
		plugins_url( $tmb_block_path, __FILE__ ),
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n' ],
		filemtime( TMB_DIR_PATH . $tmb_block_path ),
		true
	);
}

/**
 * Hook scripts function into block editor hook
 */
add_action( 'enqueue_block_assets', 'tmb_scripts' );

/**
 * Enqueue block scipt JavaScript
 */
function tmb_block_script_init() {
    wp_register_script(
        'the-marquee-block-js',
        plugins_url( 'build/index.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor' )
    );
    wp_enqueue_script( 'the-marquee-block-js');
}

/**
 * Hook scripts function into init hook
 */
add_action( 'init', 'tmb_block_script_init' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_the_marquee_block_block_init() {

	if ( !function_exists( 'register_block_type' ) ) {
		add_action( 'admin_notices', 'gutenberg_missing_notice' );
		return;
	}

	register_block_type(
		__DIR__ . '/build',
		array(
			'attributes'      => array(
				'message'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'position'        => array(
					'type'    => 'string',
					'default' => 'no',
				),
				'textColor'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'backgroundColor' => array(
					'type'    => 'string',
					'default' => '',
				),
				'fontSize'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'fontName'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'variant'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'hoverStop'       => array(
					'type'    => 'boolean',
					'default' => '',
				),
				'marDirection'    => array(
					'type'    => 'boolean',
					'default' => '',
				),
				'blockMargin'     => array(
					'type'    => 'boolean',
					'default' => '',
				),
				'scrollingSpeed'  => array(
					'type'    => 'number',
					'default' => '',
				),
			),
			'render_callback' => 'tmb_google_fonts_block_render',
			'editor_script' => 'the-marquee-block-js'
		)
	);
}

add_action( 'init', 'create_block_the_marquee_block_block_init' );

/**
 * Hook scripts function into block editor hook
 *
 * @return All fonts array
 */
function tmb_fonts_array() {

	$fonts = array();

	$fonts_json = file_get_contents( TMB_DIR_PATH . 'src/googleFonts.json' );

	$fonts_array = json_decode( $fonts_json, true );

	foreach ( $fonts_array['items'] as $key => $font ) {
		$variants_remove = array(
			'100italic',
			'200italic',
			'300italic',
			'400italic',
			'500italic',
			'600italic',
			'700italic',
			'800italic',
			'900italic',
			'100i',
			'200i',
			'300i',
			'400i',
			'500i',
			'600i',
			'700i',
			'800i',
			'900i',
		);

		$font['v'] = array_diff( $font['v'], $variants_remove );

		$font['v'] = array_flip( $font['v'] );

		$fonts_array['items'][ $key ] = $font;
	}

	foreach ( $fonts_array['items'] as $font ) {
		$id           = trim( strtolower( str_replace( ' ', '-', $font['f'] ) ) );
		$fonts[ $id ] = $font;
	}

	return $fonts;

}

/**
 * Front end render function for 'the-marquee-block'.
 *
 * @param array $attributes The block attributes.
 * @param array $content The loaded page content.
 */
function tmb_google_fonts_block_render( $attributes, $content ) {

	$font_id = isset( $attributes['fontName'] ) ? sanitize_text_field( $attributes['fontName'] ) : '';
	$variant = isset( $attributes['variant'] ) ? sanitize_text_field( $attributes['variant'] ) : '';

	if ( $font_id ) {

		$font_id_standardized = str_replace( '+', '-', strtolower( $font_id ) );

		if ( array_key_exists( $font_id_standardized, tmb_fonts_array() ) ) {
			$variants = tmb_fonts_array()[ $font_id_standardized ]['v'];

			$variants_for_url = join( ',', array_keys( $variants ) );

			wp_enqueue_style( 'google-font-' . $font_id_standardized, 'https://fonts.googleapis.com/css?family=' . $font_id . ':' . $variants_for_url . '&display=swap', array(), TMB_VERSION );
		}
	}

	return $content;
}


/**
 * Load languages files
 */
function tmb_set_script_translations() {
    wp_set_script_translations( 'the-marquee-block-js', 'the-marquee-block', plugin_dir_path( __FILE__ ) . 'languages/' );
    load_plugin_textdomain( 'the-marquee-block', false, plugin_dir_path( __FILE__ ) . 'languages/' );
}

add_action('init', 'tmb_set_script_translations');
