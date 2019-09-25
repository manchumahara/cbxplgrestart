<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.codeboxr.com
 * @since             1.0.0
 * @package           cbxplgrestart
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Plugin Restart (Deactivate and Activate)
 * Plugin URI:        https://codeboxr.com/
 * Description:       Single click plugin restart - deactivate and activate
 * Version:           1.0.0
 * Author:            Codeboxr
 * Author URI:        https://www.codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxplgrestart
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
{
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
defined( 'CBXPLGRESTART_PLUGIN_NAME' ) or define( 'CBXPLGRESTART_PLUGIN_NAME', 'cbxplgrestart' );
defined( 'CBXPLGRESTART_PLUGIN_VERSION' ) or define( 'CBXPLGRESTART_PLUGIN_VERSION', '1.0.0' );
defined( 'CBXPLGRESTART_BASE_NAME' ) or define( 'CBXPLGRESTART_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXPLGRESTART_ROOT_PATH' ) or define( 'CBXPLGRESTART_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXPLGRESTART_ROOT_URL' ) or define( 'CBXPLGRESTART_ROOT_URL', plugin_dir_url( __FILE__ ) );

if(!class_exists('CBXPluginRestart')){
	/**
	 * Restart plugin class
	 *
	 * Class CBXPluginRestart
	 */
	class CBXPluginRestart {

		public function __construct() {

			load_plugin_textdomain( 'cbxplgrestart', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			add_filter('plugin_action_links', array($this, 'on_plugin_action_links'), 10, 2);
			add_action('admin_init', array($this, 'admin_init_restart'));
		}

		/**
		 * plugin_action_links action callback
		 */
		public function on_plugin_action_links( $links, $plugin = '' ){
			if(current_user_can( 'activate_plugins' ) && $plugin != ''){
				try {
					//write_log($plugin);
					if( $plugin && is_plugin_active($plugin)){
						// coerce links to array
						if( ! is_array($links) ){
							$links = $links && is_string($links) ? (array) $links : array();
						}

						$link = sprintf(admin_url('index.php?cbxplgrestart=%s'), esc_attr($plugin));
						$link_nonce = wp_nonce_url($link, 'cbxplgrestart', 'cbxplgrestart_nonce');
						$links[] = '<a href="'.esc_url($link_nonce).'">'.esc_html__('Restart','cbxplgrestart').'</a>';
					}
				}
				catch( Exception $e ){
					// $links[] = esc_html( 'Debug: '.$e->getMessage() );
				}
			}

			return $links;
		}//end method on_plugin_action_links

		/**
		 * Restart plugin
		 */
		public static function admin_init_restart(){
			if(isset($_REQUEST['cbxplgrestart']) && sanitize_text_field($_REQUEST['cbxplgrestart']) != ''){
				$plugin = esc_attr(sanitize_text_field($_REQUEST['cbxplgrestart']));

				check_admin_referer( 'cbxplgrestart', 'cbxplgrestart_nonce' );

				if(current_user_can( 'activate_plugins' ) && $plugin != '' && is_plugin_active($plugin)){
					deactivate_plugins($plugin);

					if(!is_plugin_active($plugin)){
						activate_plugins($plugin);
					}
				}

				wp_safe_redirect( admin_url( 'plugins.php?plugin_status=all' ) );
				exit();
			}
		}//end method admin_init_restart

	}//end class CBXPluginRestart
}


/**
 * Init the plugin
 *
 * @return void
 */
function cbxplgrestart_load_plugin()
{
	if(class_exists('CBXPluginRestart')){
		new CBXPluginRestart();
	}
}

add_action('plugins_loaded', 'cbxplgrestart_load_plugin', 5);