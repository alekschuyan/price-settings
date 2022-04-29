<?php

/**
 * Fired during plugin activation
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Price_Settings
 * @subpackage Price_Settings/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Price_Settings
 * @subpackage Price_Settings/includes
 * @author     Vadyus <->
 */
class Price_Settings_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
    public static function activate() {
        self::create_db_table();
    }

    public static function create_db_table () {
        global $wpdb;
        global $plugin_name_db_version;
//        $plugin_name_db_version = '1.0.0';
//        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . "price_settings";
        if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

            $sql = "CREATE TABLE " . $table_name . " (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  product_id VARCHAR(255) NULL,
	  title VARCHAR(255) NULL,
	  delivery text NULL,
	  vendor VARCHAR(255) NULL,
	  price_rozetka longtext NULL,
	  price_promua longtext NULL,
	  price_other longtext NULL,
	  status tinyint(1) DEFAULT '1' NOT NULL,
	  date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY id (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option( 'plugin_name_db_version', $plugin_name_db_version );
        }
    }

}
