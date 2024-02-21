<?php

/**
 * Wordpress ReCaptcha - Atlas Gondal
 *
 * A WordPress plugin for integrating Google reCAPTCHA on Login and Comment Forms.
 *
 * Plugin Name: Wordpress ReCaptcha
 * Description: A WordPress plugin for integrating Google reCAPTCHA on Login and Comment Forms.
 * Version: 1.0
 * Text Domain: wordpress-recaptcha
 * Author: Atlas Gondal
 * Author URI: https://AtlasGondal.com/
 *
 * @author    Atlas Gondal <Contact@AtlasGondal.com>
 * @copyright 2024 Atlas Gondal
 * @version   v.1.0 (21/02/24)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the ReCaptcha class file
require_once('classes/GoogleRecaptcha.php');

class WordPress_ReCaptcha {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_options_page'));
        add_action('init', array($this, 'check_recaptcha'));
    }

    public function register_settings() {
        add_option('wp_recaptcha_site_key', '');
        add_option('wp_recaptcha_secret_key', '');
        register_setting('wp_recaptcha_options_group', 'wp_recaptcha_site_key');
        register_setting('wp_recaptcha_options_group', 'wp_recaptcha_secret_key');
    }

    public function register_options_page() {
        add_options_page('Wordpress ReCaptcha', 'Wordpress ReCaptcha', 'manage_options', 'wordpress-recaptcha', array($this, 'options_page'));
    }

    public function options_page() {
        ?>
        <div>
        <h2>WP ReCaptcha Settings</h2>
        <form method="post" action="options.php">
          <?php settings_fields('wp_recaptcha_options_group'); ?>
          <table>
            <tr>
              <th scope="row"><label for="wp_recaptcha_site_key">Site Key</label></th>
              <td><input type="text" id="wp_recaptcha_site_key" name="wp_recaptcha_site_key" value="<?php echo get_option('wp_recaptcha_site_key'); ?>" /></td>
            </tr>
            <tr>
              <th scope="row"><label for="wp_recaptcha_secret_key">Secret Key</label></th>
              <td><input type="text" id="wp_recaptcha_secret_key" name="wp_recaptcha_secret_key" value="<?php echo get_option('wp_recaptcha_secret_key'); ?>" /></td>
            </tr>
          </table>
          <?php submit_button(); ?>
        </form>
        </div>
        <?php
    }

    public function check_recaptcha() {
        $site_key = get_option('wp_recaptcha_site_key');
        $secret_key = get_option('wp_recaptcha_secret_key');

        if (!empty($site_key) && !empty($secret_key)) {
            $recaptcha = new GoogleRecaptcha($site_key, $secret_key);
        }
    }
}

// Instantiate the plugin class.
$wp_recaptcha_plugin = new WordPress_ReCaptcha();
