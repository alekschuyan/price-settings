<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       -
 * @since      1.0.0
 *
 * @package    Price_Settings
 * @subpackage Price_Settings/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="overlay">
    <div class="loader">
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        <div class="loader_text">Загрузка... <br/>Дождитесь завершения загрузки.</div>
    </div>
</div>
<form method="post" name="my_options" class="my_options" action="options.php">

    <?php
    $options = get_option($this->plugin_name);

    global $wpdb;
//    get_products_fields($query); // get product fields values (for the first time) from ACF

    $table_name = $wpdb->prefix . "price_settings";
    $price_settings = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id ASC" );
    $products = [];

    foreach ($price_settings as $setting) {
        $products[$setting->product_id]['delivery'] = $setting->delivery;
        $products[$setting->product_id]['vendor']   = $setting->vendor;
        $products[$setting->product_id]['status']   = $setting->status;
        $products[$setting->product_id]['price_rozetka'] = unserialize($setting->price_rozetka);
        $products[$setting->product_id]['price_promua']  = unserialize($setting->price_promua);
        $products[$setting->product_id]['price_other']   = unserialize($setting->price_other);
    }

    settings_fields( $this->plugin_name );
    do_settings_sections( $this->plugin_name );

    function get_products_fields($query) {
        if($query->have_posts()) {
            $data = [];
            while ($query->have_posts()) {
                $query->the_post();
                $data[get_the_ID()] = [
                    'product_id' => get_the_ID(),
                    'title' => get_the_title(),
                    'delivery' => get_field('delivery', get_the_ID()),
                    'vendor' => get_field('vendor', get_the_ID()),
                    'price_rozetka' => [
                        'show_in_price' => get_field('show_in_price', get_the_ID()),
                        'available' => get_field('available', get_the_ID()),
                        'price' => get_field('Price', get_the_ID()),
                        'price_old' => get_field('price_old', get_the_ID()),
                        'price_promo' => get_field('price_promo', get_the_ID()),
                        'stock_quantity' => get_field('stock_quantity', get_the_ID()),
                    ],
                    'price_promua' => [
                        'show_in_price' => get_field('show_in_price_promua', get_the_ID()),
                        'available' => get_field('available_promua', get_the_ID()),
                        'price' => get_field('price_promua', get_the_ID()),
                        'price_old' => get_field('price_old_promua', get_the_ID()),
                        'price_promo' => get_field('price_promo_promua', get_the_ID()),
                        'stock_quantity' => get_field('stock_quantity_promua', get_the_ID()),
                    ],
                    'price_other' => [
                        'show_in_price' => get_field('show_in_price_other', get_the_ID()),
                        'available' => get_field('available_other', get_the_ID()),
                        'price' => get_field('price_other', get_the_ID()),
                        'price_old' => get_field('price_old_other', get_the_ID()),
                        'price_promo' => get_field('price_promo_other', get_the_ID()),
                        'stock_quantity' => get_field('stock_quantity_other', get_the_ID()),
                    ],
                ];
            }

            global $wpdb;
            $table = 'price_settings';
            $table_name = $wpdb->prefix . $table;
            $wpdb->query('TRUNCATE TABLE ' . $table_name);

            foreach ($data as $product) {
                $wpdb->insert( $table_name,
                    [
                        'product_id' => $product['product_id'],
                        'title'      => esc_sql($product['title']),
                        'delivery'   => esc_sql($product['delivery']),
                        'vendor'     => esc_sql($product['vendor']),
                        'price_rozetka' => serialize($product['price_rozetka']),
                        'price_promua'  => serialize($product['price_promua']),
                        'price_other'   => serialize($product['price_other']),
                        'date_added'    => current_time('mysql')
                    ]
                );
            }
        }
    }

    $args = [
        'taxonomy'      => 'category',
        'orderby'       => 'name',
        'order'         => 'ASC',
        'hide_empty'    => true,
        'child_of'      => 7,
    ];

    $categories = get_terms( $args );

    ?>

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="save_prices">
        <?php submit_button(__('Сохранить изменения', $this->plugin_name), 'primary','submit', TRUE); ?>
    </div>
    <div class="notice notice-info inline" style="margin-left: 0;">
        <p>
            <?php esc_attr_e( 'При сохранении автоматически обновятся все прайсы!', 'price-settings' );
            ?>
        </p>
        <p>
            <?php esc_attr_e( 'Для поиска товаров на странице и быстрого перехода между ними используйте сочетание клавиш Ctrl+F и Enter.', 'price-settings' );
            ?>
        </p>
    </div>

    <br class="clear" />
    <h3>Список товаров из раздела Продукция:</h3>
    <table class="products_table widefat">
        <thead>
            <tr>
                <th style="width: 20px;"><strong><?php esc_attr_e( '№ п/п', 'price-settings' ); ?></strong></th>
                <th style="width: 100px;"><strong><?php esc_attr_e( 'Название товара / Категории', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Доставка/оплата, Производитель', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Прайс Rozetka', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Прайс Prom.ua', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Запасной прайс', 'price-settings' ); ?></strong></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($categories) {
                $num = 1;
                $repeat_arr = [];
                foreach( $categories as $category ) { ?>
                    <tr class="category_row">
                        <td colspan="8" class="category_col"><?=$category->name;?></td>
                    </tr>
                <?php
                    $args = [
                        'posts_per_page' => -1,
                        'orderby'     => 'id',
                        'order'       => 'ASC',
                        'post_type'   => 'store',
                        'post_status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'category',
                                'field'    => 'slug',
                                'terms'    => $category->slug
                            )
                        )
                    ];

                    $query = new WP_Query( $args ); // get products by category slug

                if($query->have_posts()) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        if(in_array(get_the_ID(), $repeat_arr)) {
                            continue;
                        } else {
                            $repeat_arr[] = get_the_ID();
                        }
                        if($num % 2 == 0) $tr_class = "alternate";
                        else $tr_class = "";
                        ?>
                        <tr class="<?=$tr_class;?> product_row row-<?=get_the_ID();?>" data-id="<?=get_the_ID();?>">
                            <td class="row-title"><?=$num;?>.</td>
                            <td>
                                <a href="<?php the_permalink(); ?>" target="_blank" title="<?php esc_attr_e( 'Просмотреть товар в новой вкладке', 'price-settings' ); ?>"><?php the_title();?></a>
                                <input type="hidden" value="<?=get_the_ID();?>" data-name="product_id" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][product_id]">
                                <input type="hidden" value="<?php the_title();?>" data-name="title" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][title]">
                                <br/><br/>
                                <strong><?php esc_attr_e( 'Категории товара:', 'price-settings' ); ?></strong>
                                <div>
                                    <?php
                                    $categories_list = get_the_category();
                                    $categories = '';
                                    foreach ($categories_list as $cat) {
                                        $category_link = get_category_link($cat->term_id);
                                        $categories .= '<a href="'. $category_link .'" target="_blank" title="'. __( 'Просмотреть категорию в новой вкладке', 'price-settings' ) .'">' . $cat->name . '</a>,<br/>';
                                    }
                                    $categories = substr($categories, 0, -6);
                                    echo $categories; ?>
                                </div>
                                <br/>
                                <br/>
                                <?php $status = $products[get_the_ID()]['status'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$status;?>" data-name="status" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][status]" <?php checked( $status, '1', TRUE ); ?> /><?php esc_attr_e( 'Показывать товар на сайте', 'price-settings' ); ?></label>
                            </td>
                            <td>
                                <strong><?php esc_attr_e( 'Доставка/оплата:', 'price-settings' ); ?></strong>
                                <textarea data-name="delivery" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][delivery]" id="<?php echo $this->plugin_name;?>-delivery-<?=get_the_ID();?>" cols="20" rows="6" style="resize: none;"><?php echo $products[get_the_ID()]['delivery'];?></textarea>
                                <br/><br/>
                                <strong><?php esc_attr_e( 'Производитель:', 'price-settings' ); ?></strong>
                                <div>(По умолчанию iBOARD)</div>
                                <div>
                                    <input style="width: 190px;" type="text" data-name="vendor" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][vendor]" id="<?php echo $this->plugin_name;?>-vendor-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['vendor'];?>">
                                </div>
                            </td>
                            <td>
                                <?php $value = $products[get_the_ID()]['price_rozetka']['show_in_price'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_rozetka-show_in_price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][show_in_price]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Не показывать товар в прайсе Rozetka', 'price-settings' ); ?></label>
                                <br>
                                <?php $value = $products[get_the_ID()]['price_rozetka']['available'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_rozetka-available" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][available]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Есть в наличии?', 'price-settings' ); ?></label>
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price-<?=get_the_ID();?>"><?php esc_attr_e( 'Стандартная цена, грн:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_rozetka-price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][price]" id="<?php echo $this->plugin_name;?>-price-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_rozetka']['price'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_old-<?=get_the_ID();?>"><?php esc_attr_e( 'Старая цена, грн. (перечеркнутая):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_rozetka-price_old" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][price_old]" id="<?php echo $this->plugin_name;?>-price_old-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_rozetka']['price_old'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_promo-<?=get_the_ID();?>"><?php esc_attr_e( 'Промо цена, грн. (для промоакции):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_rozetka-price_promo" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][price_promo]" id="<?php echo $this->plugin_name;?>-price_promo-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_rozetka']['price_promo'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-stock_quantity-<?=get_the_ID();?>"><?php esc_attr_e( 'Количество товаров для промоакции:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_rozetka-stock_quantity" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_rozetka][stock_quantity]" id="<?php echo $this->plugin_name;?>-stock_quantity-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_rozetka']['stock_quantity'] ?? '';?>">
                            </td>
                            <td><?php $value = $products[get_the_ID()]['price_promua']['show_in_price'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_promua-show_in_price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][show_in_price]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Не показывать товар в прайсе Prom.ua', 'price-settings' ); ?></label>
                                <br>
                                <?php $value = $products[get_the_ID()]['price_promua']['available'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_promua-available" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][available]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Есть в наличии?', 'price-settings' ); ?></label>
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_promua-<?=get_the_ID();?>"><?php esc_attr_e( 'Стандартная цена, грн:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_promua-price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][price]" id="<?php echo $this->plugin_name;?>-price_promua-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_promua']['price'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_old_promua-<?=get_the_ID();?>"><?php esc_attr_e( 'Старая цена, грн. (перечеркнутая):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_promua-price_old" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][price_old]" id="<?php echo $this->plugin_name;?>-price_old_promua-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_promua']['price_old'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_promo_promua-<?=get_the_ID();?>"><?php esc_attr_e( 'Промо цена, грн. (для промоакции):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_promua-price_promo" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][price_promo]" id="<?php echo $this->plugin_name;?>-price_promo_promua-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_promua']['price_promo'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-stock_quantity_promua-<?=get_the_ID();?>"><?php esc_attr_e( 'Количество товаров для промоакции:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_promua-stock_quantity" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_promua][stock_quantity]" id="<?php echo $this->plugin_name;?>-stock_quantity_promua-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_promua']['stock_quantity'] ?? '';?>">
                            </td>
                            <td>
                                <?php $value = $products[get_the_ID()]['price_other']['show_in_price'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_other-show_in_price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][show_in_price]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Не показывать товар в Запасном прайсе', 'price-settings' ); ?></label>
                                <br>
                                <?php $value = $products[get_the_ID()]['price_other']['available'] ?? 0; ?>
                                <label><input type="checkbox" value="<?=$value;?>" data-name="price_other-available" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][available]" <?php checked( $value, '1', TRUE ); ?> /><?php esc_attr_e( 'Есть в наличии?', 'price-settings' ); ?></label>
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_other-<?=get_the_ID();?>"><?php esc_attr_e( 'Стандартная цена, грн:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_other-price" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][price]" id="<?php echo $this->plugin_name;?>-price_other-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_other']['price'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_old_other-<?=get_the_ID();?>"><?php esc_attr_e( 'Старая цена, грн. (перечеркнутая):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_other-price_old" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][price_old]" id="<?php echo $this->plugin_name;?>-price_old_other-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_other']['price_old'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-price_promo_other-<?=get_the_ID();?>"><?php esc_attr_e( 'Промо цена, грн. (для промоакции):', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_other-price_promo" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][price_promo]" id="<?php echo $this->plugin_name;?>-price_promo_other-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_other']['price_promo'] ?? '';?>">
                                <br>
                                <label for="<?php echo $this->plugin_name;?>-stock_quantity_other-<?=get_the_ID();?>"><?php esc_attr_e( 'Количество товаров для промоакции:', 'price-settings' ); ?></label>
                                <input style="width: 120px;" type="text" data-name="price_other-stock_quantity" name="<?php echo $this->plugin_name;?>[<?=get_the_ID();?>][price_other][stock_quantity]" id="<?php echo $this->plugin_name;?>-stock_quantity_other-<?=get_the_ID();?>" value="<?php echo $products[get_the_ID()]['price_other']['stock_quantity'] ?? '';?>">
                            </td>
                        </tr>
                        <?php $num++; }
                    wp_reset_query();
                } else { ?>
                    <tr>
                        <td colspan="8"><h4>Товары отсутствуют.</h4></td>
                    </tr>
                <?php }
                }
            }
        ?>

        </tbody>
        <tfoot>
            <tr>
                <th><strong><?php esc_attr_e( '№ п/п', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Название товара / Категории', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Доставка/оплата, Производитель', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Прайс Rozetka', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Прайс Prom.ua', 'price-settings' ); ?></strong></th>
                <th><strong><?php esc_attr_e( 'Запасной прайс', 'price-settings' ); ?></strong></th>
            </tr>
        </tfoot>
    </table>

    <div class="save_prices">
        <?php submit_button(__('Сохранить изменения', $this->plugin_name), 'primary','submit', TRUE); ?>
    </div>
    <div class="notice notice-info inline" style="margin-left: 0;">
        <p>
            <?php esc_attr_e( 'При сохранении автоматически обновятся все прайсы!', 'price-settings' );
            ?>
        </p>
    </div>

</form>

<p id="back-top" title="Вверх страницы" style=""><a href="#top"></a></p>
