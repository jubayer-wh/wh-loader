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
    $hex_args = array('sanitize_callback' => 'sanitize_hex_color');
    
    register_setting('wh-loader-group', 'wh_loader_brand_name', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'WEBKIH'));
    register_setting('wh-loader-group', 'wh_loader_bg_color', array_merge($hex_args, array('default' => '#032844')));
    register_setting('wh-loader-group', 'wh_loader_text_color', array_merge($hex_args, array('default' => '#ffffff')));
    register_setting('wh-loader-group', 'wh_loader_spinner_active', array_merge($hex_args, array('default' => '#ffffff')));
    register_setting('wh-loader-group', 'wh_loader_display_mode', array('sanitize_callback' => 'wh_loader_sanitize_display_mode', 'default' => 'all'));
    register_setting('wh-loader-group', 'wh_loader_custom_pages', array('sanitize_callback' => 'wh_loader_sanitize_custom_pages', 'default' => ''));
}

function wh_loader_sanitize_display_mode($value) {
    $allowed = array('home', 'all', 'custom');
    $value = sanitize_text_field($value);
    return in_array($value, $allowed, true) ? $value : 'all';
}

function wh_loader_sanitize_custom_pages($value) {
    $value = sanitize_textarea_field($value);
    $items = preg_split('/[\r\n,]+/', $value);
    $items = array_filter(array_map('trim', $items));
    return implode(', ', $items);
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
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Load On', 'wh-loader'); ?></th>
                    <td>
                        <?php $display_mode = get_option('wh_loader_display_mode', 'all'); ?>
                        <select name="wh_loader_display_mode">
                            <option value="home" <?php selected($display_mode, 'home'); ?>><?php esc_html_e('Homepage only', 'wh-loader'); ?></option>
                            <option value="all" <?php selected($display_mode, 'all'); ?>><?php esc_html_e('All pages', 'wh-loader'); ?></option>
                            <option value="custom" <?php selected($display_mode, 'custom'); ?>><?php esc_html_e('Custom pages', 'wh-loader'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose where the loader is shown.', 'wh-loader'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom Pages', 'wh-loader'); ?></th>
                    <td>
                        <textarea name="wh_loader_custom_pages" rows="4" cols="50"><?php echo esc_textarea(get_option('wh_loader_custom_pages', '')); ?></textarea>
                        <p class="description"><?php esc_html_e('For "Custom pages", enter page IDs or slugs separated by commas or new lines. Example: 12, about-us, contact', 'wh-loader'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function wh_loader_should_display() {
    $display_mode = get_option('wh_loader_display_mode', 'all');

    if ($display_mode === 'home') {
        return is_front_page() || is_home();
    }

    if ($display_mode === 'custom') {
        if (!is_page()) {
            return false;
        }

        $raw_custom_pages = get_option('wh_loader_custom_pages', '');
        if (empty($raw_custom_pages)) {
            return false;
        }

        $items = preg_split('/[\r\n,]+/', $raw_custom_pages);
        $items = array_filter(array_map('trim', $items));
        $identifiers = array();

        foreach ($items as $item) {
            $identifiers[] = ctype_digit($item) ? (int) $item : sanitize_title($item);
        }

        if (empty($identifiers)) {
            return false;
        }

        return is_page($identifiers);
    }

    return true;
}

/**
 * 5. Enqueue Scripts and Styles
 */
add_action('wp_enqueue_scripts', 'wh_loader_enqueue_assets');
function wh_loader_enqueue_assets() {
    if (!wh_loader_should_display()) {
        return;
    }

    wp_register_style('wh-loader-style', false, array(), '1.1');
    wp_enqueue_style('wh-loader-style');

    // Get Options
    $bg      = get_option('wh_loader_bg_color', '#032844');
    $text    = get_option('wh_loader_text_color', '#ffffff');
    $active  = get_option('wh_loader_spinner_active', '#ffffff');
    // Static idle color - hardcoded string, safe to use directly
    $idle    = 'rgba(255, 255, 255, 0.2)';

    // Build CSS with "Late Escaping" using esc_attr()
    // We break out of the string and use concatenation . esc_attr($var) . 
    $custom_css = "
        #wh-loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: " . esc_attr($bg) . ";
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            z-index: 9999999; transition: opacity 0.6s ease, visibility 0.6s;
        }
        .wh-container { display: flex; flex-direction: column; align-items: center; gap: 35px; }
        .wh-win-loader { display: grid; grid-template-columns: repeat(2, 50px); grid-template-rows: repeat(2, 50px); gap: 8px; }
        .wh-pane {
            width: 50px; height: 50px; background-color: " . $idle . ";
            border-radius: 3px; animation: wh-circle-step 2.4s linear infinite;
        }
        .wh-pane:nth-child(1) { animation-delay: 0s; }
        .wh-pane:nth-child(2) { animation-delay: 0.6s; }
        .wh-pane:nth-child(4) { animation-delay: 1.2s; }
        .wh-pane:nth-child(3) { animation-delay: 1.8s; }
        .wh-brand-name {
            color: " . esc_attr($text) . ";
            font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;
            font-size: 26px; font-weight: 300; letter-spacing: 8px; text-transform: uppercase;
            animation: wh-pulse-text 2.4s ease-in-out infinite;
        }
        @keyframes wh-circle-step {
            0%, 100% { background-color: " . $idle . "; }
            25% { background-color: " . esc_attr($active) . "; }
            50% { background-color: " . $idle . "; }
        }
        @keyframes wh-pulse-text {
            0%, 100% { opacity: 0.4; transform: scale(0.97); }
            50% { opacity: 1; transform: scale(1); }
        }
        .wh-loader-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
    ";

    wp_add_inline_style('wh-loader-style', $custom_css);

    wp_register_script('wh-loader-script', false, array(), '1.1', true);
    wp_enqueue_script('wh-loader-script');

    $custom_js = '
        window.addEventListener("load", function() {
            var el = document.getElementById("wh-loader-wrapper");
            if(el) {
                setTimeout(function(){ el.classList.add("wh-loader-hidden"); }, 400);
            }
        });
    ';

    wp_add_inline_script('wh-loader-script', $custom_js);
}

/**
 * 6. Output HTML Markup in Footer
 */
add_action('wp_footer', 'wh_loader_html_markup');
function wh_loader_html_markup() {
    if (!wh_loader_should_display()) {
        return;
    }

    $brand = get_option('wh_loader_brand_name', 'WEBKIH');
    ?>
    <div id="wh-loader-wrapper">
        <div class="wh-container">
            <div class="wh-win-loader">
                <div class="wh-pane"></div>
                <div class="wh-pane"></div>
                <div class="wh-pane"></div>
                <div class="wh-pane"></div>
            </div>
            <div class="wh-brand-name"><?php echo esc_html($brand); ?></div>
        </div>
    </div>
    <?php
}
