<?php
/**
 * Plugin Name: WH Loader
 * Plugin URI: https://github.com/jubayer-wh/wh-loader/
 * Description: A sleek Windows 11 style preloader for your website. Fully customizable brand name and colors via the WordPress dashboard.
 * Version: 1.1
 * Author: Jubayer Hossain
 * Author URI: https://webkih.com/about/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wh-loader
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1. Add 'Settings' link to the plugin action links
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wh_loader_action_links');
function wh_loader_action_links($links) {
    $settings_link = '<a href="options-general.php?page=wh-loader-settings">' . __('Settings', 'wh-loader') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * 2. Create the Settings Menu
 */
add_action('admin_menu', 'wh_loader_create_menu');
function wh_loader_create_menu() {
    add_options_page(
        __('WH Loader Settings', 'wh-loader'), 
        __('WH Loader', 'wh-loader'), 
        'manage_options', 
        'wh-loader-settings', 
        'wh_loader_settings_page'
    );
}

/**
 * 3. Register and Sanitize Settings
 */
add_action('admin_init', 'wh_loader_register_settings');
function wh_loader_register_settings() {
    register_setting('wh-loader-group', 'wh_loader_brand_name', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'WEBKIH'));
    register_setting('wh-loader-group', 'wh_loader_bg_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#032844'));
    register_setting('wh-loader-group', 'wh_loader_text_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#ffffff'));
    register_setting('wh-loader-group', 'wh_loader_spinner_active', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#ffffff'));
}

/**
 * 4. Build the Settings Page UI
 */
function wh_loader_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('WH Loader Settings', 'wh-loader'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('wh-loader-group'); ?>
            <?php do_settings_sections('wh-loader-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Brand Name', 'wh-loader'); ?></th>
                    <td><input type="text" name="wh_loader_brand_name" value="<?php echo esc_attr(get_option('wh_loader_brand_name', 'WEBKIH')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Background Color', 'wh-loader'); ?></th>
                    <td><input type="color" name="wh_loader_bg_color" value="<?php echo esc_attr(get_option('wh_loader_bg_color', '#032844')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Text Color', 'wh-loader'); ?></th>
                    <td><input type="color" name="wh_loader_text_color" value="<?php echo esc_attr(get_option('wh_loader_text_color', '#ffffff')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Active Spinner Color', 'wh-loader'); ?></th>
                    <td><input type="color" name="wh_loader_spinner_active" value="<?php echo esc_attr(get_option('wh_loader_spinner_active', '#ffffff')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * 5. Inject Loader into Frontend
 */
add_action('wp_footer', 'wh_loader_inject');
function wh_loader_inject() {
    $bg      = get_option('wh_loader_bg_color', '#032844');
    $text    = get_option('wh_loader_text_color', '#ffffff');
    $active  = get_option('wh_loader_spinner_active', '#ffffff');
    $brand   = get_option('wh_loader_brand_name', 'WEBKIH');
    $idle    = 'rgba(255, 255, 255, 0.2)'; 
    ?>
    <style>
        #wh-loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: <?php echo esc_attr($bg); ?>;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            z-index: 9999999; transition: opacity 0.6s ease, visibility 0.6s;
        }
        .wh-container { display: flex; flex-direction: column; align-items: center; gap: 35px; }
        .wh-win-loader { display: grid; grid-template-columns: repeat(2, 50px); grid-template-rows: repeat(2, 50px); gap: 8px; }
        .wh-pane {
            width: 50px; height: 50px; background-color: <?php echo esc_attr($idle); ?>;
            border-radius: 3px; animation: wh-circle-step 2.4s linear infinite;
        }
        .wh-pane:nth-child(1) { animation-delay: 0s; }
        .wh-pane:nth-child(2) { animation-delay: 0.6s; }
        .wh-pane:nth-child(4) { animation-delay: 1.2s; }
        .wh-pane:nth-child(3) { animation-delay: 1.8s; }
        .wh-brand-name {
            color: <?php echo esc_attr($text); ?>;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 26px; font-weight: 300; letter-spacing: 8px; text-transform: uppercase;
            animation: wh-pulse-text 2.4s ease-in-out infinite;
        }
        @keyframes wh-circle-step {
            0%, 100% { background-color: <?php echo esc_attr($idle); ?>; }
            25% { background-color: <?php echo esc_attr($active); ?>; }
            50% { background-color: <?php echo esc_attr($idle); ?>; }
        }
        @keyframes wh-pulse-text {
            0%, 100% { opacity: 0.4; transform: scale(0.97); }
            50% { opacity: 1; transform: scale(1); }
        }
        .wh-loader-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
    </style>
    <div id="wh-loader-wrapper">
        <div class="wh-container">
            <div class="wh-win-loader"><div class="wh-pane"></div><div class="wh-pane"></div><div class="wh-pane"></div><div class="wh-pane"></div></div>
            <div class="wh-brand-name"><?php echo esc_html($brand); ?></div>
        </div>
    </div>
    <script>
        window.addEventListener("load", function() {
            var el = document.getElementById("wh-loader-wrapper");
            if(el) setTimeout(function(){ el.classList.add("wh-loader-hidden"); }, 400);
        });
    </script>
    <?php
}