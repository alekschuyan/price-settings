<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Price_Settings
 * @subpackage Price_Settings/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Price_Settings
 * @subpackage Price_Settings/admin
 * @author     Vadyus <->
 */
class Price_Settings_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Settings_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Settings_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/price-settings-admin.css', array(), $this->version * 2, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Settings_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Settings_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/price-settings-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */

    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
        */
        add_options_page( 'Настройки товаров для прайсов', 'Настройки прайсов', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     */

    public function add_action_links( $links ) {

        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    /**
     * Render the settings page for this plugin.
     */

    public function display_plugin_setup_page() {

        include_once( 'partials/price-settings-admin-display.php' );

    }

    /**
     * Validate options
     */
    public function validate($input) {
        $valid = array();

        if(isset($input) && !empty($input)) {
            $table_name = 'price_settings';
            $this->truncate_table($table_name);
            $this->insert_table($table_name, $input);
            $this->update_prices();
        }

        return $valid;
    }

    /**
     * Truncate table
     */
    public function truncate_table($table = '') {
        global $wpdb;
        if($table === '')
            $table = 'price_settings';

        $table_name = $wpdb->prefix . $table;
        $wpdb->query('TRUNCATE TABLE ' . $table_name);
    }

    /**
     * Insert table
     */
    public function insert_table($table, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;

        $wpdb->insert( $table_name,
            [
                'product_id' => $data['product_id'],
                'title'      => esc_sql($data['title']),
                'delivery'   => esc_sql($data['delivery']),
                'vendor'     => esc_sql($data['vendor']),
                'status'     => esc_sql($data['status']),
                'price_rozetka' => serialize($data['price_rozetka']),
                'price_promua'  => serialize($data['price_promua']),
                'price_other'   => serialize($data['price_other']),
                'date_added'    => current_time('mysql')
            ]
        );

//        foreach ($data as $product) {
//
//            $wpdb->insert( $table_name,
//                [
//                    'product_id' => $product['product_id'],
//                    'title'      => esc_sql($product['title']),
//                    'delivery'   => esc_sql($product['delivery']),
//                    'vendor'     => esc_sql($product['vendor']),
//                    'price_rozetka' => serialize($product['price_rozetka']),
//                    'price_promua'  => serialize($product['price_promua']),
//                    'price_other'   => serialize($product['price_other']),
//                    'date_added'    => current_time('mysql')
//                ]
//            );
//        }
    }

    /**
     * Update prices
     */
    public function update_prices() {
        $urls = [
            '/?page_id=491', // price rozetka
            '/?page_id=792', // price promua
            '/?page_id=839' // price other
        ];

        foreach ($urls as $url) {
            wp_remote_get(get_home_url() . $url);
        }
    }

    /**
     * Update all options
     */
    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    /**
     * Ajax action js
     */
    public function action_javascript() {
        ?>
            <script>
                jQuery(document).ready(function($) {

                    $('.save_prices .button.button-primary').on('click', function(e) {
                        e.preventDefault();
                        $('.overlay').fadeIn();
                        let product_row = $('.products_table .product_row');
                        let k = 0;
                        let i = 0;
                        let len = product_row.length;
                        console.log(len);
                        product_row.each(function(index) {

                            if(k === 0) truncate_table();
                            k++;

                            let row = $(this);
                            let id = row.data('id');
                            let form_data = {};

                            $('.row-' + id).find('input, textarea').each(function() {
                                let input = $(this);
                                let input_value = 0;
                                if(input.attr('type') && input.attr('type') === 'checkbox') {
                                    if(input.prop('checked')) {
                                        input_value = 1;
                                    } else {
                                        input_value = 0;
                                    }
                                } else {
                                    input_value = input.val();
                                }
                                form_data[input.data('name')] = input_value;
                            });

                            let data = {
                                action: 'custom_action',
                                form_data: form_data
                            };

                            setTimeout(function(){
                                $.ajax({
                                    url: ajaxurl,
                                    type: "post",
                                    data: data ,
                                    success: function (response) {
                                        i++;
                                        // console.log(i);
                                        // console.log(id + ' обновлено.');
                                        if(i === len) {
                                            update_prices();
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.log(textStatus, errorThrown);
                                    }
                                });
                            }, 500 * index);
                        });

                    });

                    function truncate_table() {
                        let data = {
                            action: 'truncate_action'
                        };

                        $.ajax({
                            url: ajaxurl,
                            type: "post",
                            data: data ,
                            success: function (response) {

                            }
                        });
                    }

                    function update_prices() {
                        let data = {
                            action: 'update_action'
                        };

                        $.ajax({
                            url: ajaxurl,
                            type: "post",
                            data: data ,
                            success: function (response) {
                                $('.overlay').fadeOut();
                                alert('Изменения сохранены, все прайсы обновлены!');
                            }
                        });
                    }
                });
            </script>
        <?php
    }

    /**
     * Ajax action callback
     */
    public function action_callback() {
        $new_array = [];
        if(isset($_POST['form_data']) && $_POST['form_data']) {
            $form_data = $_POST['form_data'];
            foreach ($form_data as $key => $value) {
                $check_key = stripos($key, '-');
                if ($check_key !== false) {
                    $tmp = explode('-', $key);
                    if(isset($tmp[0]) && isset($tmp[1])) {
                        $new_array[$tmp[0]][$tmp[1]] = $value;
                    }
                } else {
                    $new_array[$key] = $value;
                }
            }
        }

        if(!empty($new_array)) {
            $table_name = 'price_settings';
            $this->insert_table($table_name, $new_array);
        }

        wp_die();
    }

}
