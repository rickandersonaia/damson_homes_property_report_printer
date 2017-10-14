<?php
/**
 * Plugin Name: Damson Homes Property Report Generator
 * Plugin URI:  https://github.com/rickandersonaia/damson_homes_property_report_printer
 * Description: Prints a formatted property report in PDF format including all media library attachments
 * Version:     0.0.1
 * Author:      Rick Anderson
 * Author URI:  https://www.byobwebsite.com
 * Donate link: https://github.com/rickandersonaia/damson_homes_property_report_printer
 * License:     GPLv2
 * Text Domain: damson-homes-property-report-generator
 * Domain Path: /languages
 *
 * @link    https://github.com/rickandersonaia/damson_homes_property_report_printer
 *
 * @package DH_Propery_Report_Generator
 * @version 0.0.1
 *
 */

/**
 * Copyright (c) 2017 Rick Anderson (email : rick@byobwebsite.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Main initiation class.
 *
 * @since  0.0.1
 */

use \setasign\Fpdi;

final class DH_Propery_Report_Generator {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	const VERSION = '0.0.1';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $basename = '';

	public $post_id = 0;

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.1
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    DH_Propery_Report_Generator
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  DH_Propery_Report_Generator A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.1
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		define( 'VENDOR_PATH', $this->path . 'vendor/' );
		define( 'INC_PATH', $this->path . 'includes/' );

		require_once( INC_PATH . 'dhprg_assemble_report_from_data.php' );
		require_once( INC_PATH . 'dhprg_assemble_report_from_files.php' );
		require_once( VENDOR_PATH . 'autoload.php' );

		add_action( 'template_redirect', array( $this, 'build_report' ), 98 );
	}

	public function print_link() {
//		$data = get_post_custom($this->post_id);
//		$media = get_attached_media( 'application/pdf', $this->post_id );
//		var_dump($media);

		$link   = apply_filters( 'the_permalink', get_permalink() ) . '?output=pdf';
		$script = "\n<script>
						function loadPDF(){
						    window.open(\" $link \", '_blank')
						}
					</script>\n";

		$link_text = " <a class=\"button cta has-icon pending\" href=\"$link\" target='_blank' onclick='loadPDF()'>"
		             . dh_get_svg( array( 'icon' => 'pdf' ) ) . " Generate Summary</a>\n";

		return $script . $link_text;
	}

	public function build_report() {
		global $post;
		$this->post_id = $post->ID;
		if ( isset( $_GET['output'] ) && $_GET['output'] == 'pdf' ) {

//			$report_from_files = new dhprg_assemble_report_from_files( $this->post_id );
//			$report_from_files->test();
			$report_from_data = new dhprg_assemble_report_from_data( $this->post_id);
			$report_from_data->print_report();


		}
	}


	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.1
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.1
	 */
	public function init() {

		// Load translated strings for plugin.
		load_plugin_textdomain( 'dhprg', false, dirname( $this->basename ) . '/languages/' );

	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.1
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );

		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );

		return $url . $path;
	}
}

/**
 * Grab the DH_Propery_Report_Generator object and return it.
 * Wrapper for DH_Propery_Report_Generator::get_instance().
 *
 * @since  0.0.1
 * @return DH_Propery_Report_Generator  Singleton instance of plugin class.
 */
function dhprg() {
	return DH_Propery_Report_Generator::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', 'dhprg' );

// Activation and deactivation.
register_activation_hook( __FILE__, array( dhprg(), '_activate' ) );
register_deactivation_hook( __FILE__, array( dhprg(), '_deactivate' ) );
