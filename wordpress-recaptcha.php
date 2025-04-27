<?php

/**
 * Wordpress Recaptcha - Atlas Gondal
 *
 * Simple. Secure. Lightweight reCAPTCHA protection for your WordPress site.
 *
 * Plugin Name: Wordpress Recaptcha
 * Description: Simple. Secure. Lightweight reCAPTCHA protection for your WordPress site.
 * Version: 2.0
 * Text Domain: wordpress-recaptcha
 * Author: Atlas Gondal
 * Author URI: https://AtlasGondal.com/
 *
 * @author    Atlas Gondal <Contact@AtlasGondal.com>
 * @copyright 2024 Atlas Gondal
 * @version   v.2.0 (26/04/25)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the ReCaptcha class file
require_once('classes/GoogleRecaptcha.php');

class WP_ReCaptcha {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_options_page'));
        add_action('init', array($this, 'check_recaptcha'));
		add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
    }

    public function register_settings() {
        add_option('wp_recaptcha_site_key', '');
        add_option('wp_recaptcha_secret_key', '');
		add_option('wp_recaptcha_version', 'v2');
        register_setting('wp_recaptcha_options_group', 'wp_recaptcha_site_key', 
			['sanitize_callback' => [$this, 'sanitize_site_key']
		]);
        register_setting('wp_recaptcha_options_group', 'wp_recaptcha_secret_key', [
			'sanitize_callback' => [$this, 'sanitize_secret_key']
		]);
		register_setting('wp_recaptcha_options_group', 'wp_recaptcha_version', [
			'sanitize_callback' => 'sanitize_text_field'
		]);
		
		register_setting('wp_recaptcha_options_group', 'wp_recaptcha_v3_threshold', [
			'sanitize_callback' => function($value) {
				$value = floatval($value);
				return ($value >= 0 && $value <= 1) ? $value : 0.5;
			}
		]);

    }

    public function register_options_page() {
        add_options_page('Wordpress ReCaptcha', 'Wordpress ReCaptcha', 'manage_options', 'wordpress-recaptcha', array($this, 'options_page'));
    }
	
	public function admin_assets($hook)
	{
		if ($hook !== 'settings_page_wordpress-recaptcha') {
			return;
		}

		// Enqueue admin styles and scripts only for this plugin page
		wp_enqueue_style('wp-recaptcha-admin-css', plugin_dir_url(__FILE__) . 'assets/css/wp-recaptcha-admin.css', [], '1.0');
		wp_enqueue_script('wp-recaptcha-admin-js', plugin_dir_url(__FILE__) . 'assets/js/wp-recaptcha-admin.js', ['jquery'], '1.0', true);
	}


    public function options_page()
	{
		$site_key = get_option('wp_recaptcha_site_key');
		$secret_key = get_option('wp_recaptcha_secret_key');
		?>
		<div class="wrap wp-recaptcha-settings">
			<h2>WordPress reCAPTCHA Settings</h2>
			<div class="settings-container">

				<div class="settings-main">
					<form method="post" action="options.php">
						<?php settings_fields('wp_recaptcha_options_group'); ?>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="wp_recaptcha_site_key">Site Key</label></th>
								<td>
									<input type="text" id="wp_recaptcha_site_key" name="wp_recaptcha_site_key"
										   value="<?php echo esc_attr($this->mask_key($site_key)); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wp_recaptcha_secret_key">Secret Key</label></th>
								<td>
									<input type="text" id="wp_recaptcha_secret_key" name="wp_recaptcha_secret_key"
										   value="<?php echo esc_attr($this->mask_key($secret_key)); ?>" class="regular-text" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wp_recaptcha_version">reCAPTCHA Version</label></th>
								<td>
									<select name="wp_recaptcha_version" id="wp_recaptcha_version">
										<option value="v2" <?php selected(get_option('wp_recaptcha_version'), 'v2'); ?>>v2 - Checkbox</option>
										<option value="v3" <?php selected(get_option('wp_recaptcha_version'), 'v3'); ?>>v3 - Invisible</option>
									</select>
									<p class="description">Choose reCAPTCHA version: v2 (visible checkbox) or v3 (invisible background check).</p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wp_recaptcha_v3_threshold">v3 Score Threshold</label></th>
								<td>
									<input type="number" id="wp_recaptcha_v3_threshold" name="wp_recaptcha_v3_threshold" step="0.1" min="0" max="1"
										   value="<?php echo esc_attr(get_option('wp_recaptcha_v3_threshold', '0.5')); ?>" class="small-text" />
									<p class="description">Minimum score to accept in v3 (0.0 = very likely bot, 1.0 = very likely human). Default 0.5.</p>
								</td>
							</tr>
						</table>

						<p class="description">
							To retrieve your Site and Secret Keys, visit
							<a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener noreferrer">
								Google reCAPTCHA Admin Console
							</a>.
						</p>

						<?php submit_button('Save Settings'); ?>
					</form>

				</div>

				<div class="settings-sidebar">
					<h3>Want to Support?</h3>
					<ul>
						<li><a href="https://atlasgondal.com/contact-me/?utm_source=self&utm_medium=wp&utm_campaign=wordpress-recaptcha&utm_term=hire-me" target="_blank">Hire me on a project</a></li>
						<li>Buy me a Coffee <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=YWT3BFURG6SGS&amp;source=url" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif"> </a></li>
					</ul>

					<h3>Wanna say Thanks?</h3>
					<ul>
						<li>Tweet me: <a href="https://x.com/atlas_gondal" target="_blank">@Atlas_Gondal</a></li>
					</ul>

					<h3>Got a Problem?</h3>
					<ul>
						<li><a href="https://github.com/AtlasGondal/wordpress-recaptcha/issues" target="_blank">Open an issue</a></li>
					</ul>

					<p class="sidebar-footer">
						Developed by: <strong><a href="https://AtlasGondal.com/?utm_source=self&utm_medium=wp&utm_campaign=wordpress-recaptcha&utm_term=developed-by">Atlas Gondal</a></strong>
					</p>
				</div>

			</div>
		</div>
		<?php
	}


	/**
	 * Mask the key by showing only first 30%
	 */
	private function mask_key($key)
	{
		if (empty($key)) {
			return '';
		}
		$visible_length = (int) ceil(strlen($key) * 0.3);
		$visible_part = substr($key, 0, $visible_length);
		return $visible_part . str_repeat('X', strlen($key) - $visible_length);
	}
	
	public function sanitize_site_key($value)
	{
		$current = get_option('wp_recaptcha_site_key');

		// Detect if user left masked value unchanged
		if ($this->mask_key($current) === $value) {
			return $current; // Return existing key without updating
		}

		return sanitize_text_field($value);
	}

	public function sanitize_secret_key($value)
	{
		$current = get_option('wp_recaptcha_secret_key');

		// Detect if user left masked value unchanged
		if ($this->mask_key($current) === $value) {
			return $current; // Return existing key without updating
		}

		return sanitize_text_field($value);
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
$wp_recaptcha_plugin = new WP_ReCaptcha();

