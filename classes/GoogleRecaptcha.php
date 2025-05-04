<?php

/**
 * Google reCAPTCHA integration for WordPress (Login, Registration, Reset Password, Comments)
 */
class GoogleReCaptcha
{
    private $site_key;
    private $secret_key;

    public function __construct($site_key, $secret_key)
    {
        $this->site_key = $site_key;
        $this->secret_key = $secret_key;

        if (!is_user_logged_in()) {

	    // Ensure reCAPTCHA script loads correctly on wp-login.php
            add_action('login_enqueue_scripts', [$this, 'enqueue_recaptcha_script']);
		
            add_action('login_form', [$this, 'add_login_recaptcha']);
            add_filter('wp_authenticate_user', [$this, 'validate_login_recaptcha'], 10, 2);

            add_action('register_form', [$this, 'add_registration_recaptcha']);
            add_filter('registration_errors', [$this, 'validate_registration_recaptcha'], 10, 3);

            add_action('lostpassword_form', [$this, 'add_lostpassword_recaptcha']);
            add_filter('lostpassword_errors', [$this, 'validate_lostpassword_recaptcha'], 10, 2);
            add_action('validate_password_reset', [$this, 'validate_password_reset_captcha'], 10, 2);

            add_filter('comment_form_defaults', [$this, 'add_comment_recaptcha']);
            add_action('pre_comment_on_post', [$this, 'validate_comment_recaptcha']);

            add_filter('get_comment_author_link', [$this, 'remove_comment_author_url']);
            add_filter('preprocess_comment', [$this, 'remove_urls_from_comment_content']);

            add_action('wp', [$this, 'conditionally_enqueue_for_comments']);
        }
    }

    /**
     * Conditionally enqueue reCAPTCHA scripts for comment forms
     */
    public function conditionally_enqueue_for_comments()
    {
        if (is_singular() && (comments_open() || get_comments_number())) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_recaptcha_script']);
            add_action('wp_footer', [$this, 'print_recaptcha_inline_styles']);
        }
    }

    /**
     * Enqueue reCAPTCHA script
     */
    public function enqueue_recaptcha_script()
    {
        $site_key = get_option('wp_recaptcha_site_key');
        $version = get_option('wp_recaptcha_version', 'v2');

        if (!wp_script_is('google-recaptcha', 'enqueued')) {
            $script_url = ($version === 'v3')
                ? 'https://www.google.com/recaptcha/api.js?render=' . $site_key
                : 'https://www.google.com/recaptcha/api.js';

            wp_enqueue_script('google-recaptcha', $script_url, [], null, true);
        }
    }

    /**
     * Output reCAPTCHA styles
     */
    public function print_recaptcha_inline_styles()
    {
        echo '<style>.g-recaptcha { margin-bottom: 20px; transform: scale(0.89); transform-origin: 0 0; }</style>';
    }

    /**
     * Output reCAPTCHA widget (v2 or v3)
     */
    private function render_recaptcha_widget($action = 'submit')
    {
        $site_key = get_option('wp_recaptcha_site_key');
        $version = get_option('wp_recaptcha_version', 'v2');

        if ($version === 'v2') {
            echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
        } elseif ($version === 'v3') {
            echo '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">';
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('" . esc_js($site_key) . "', {action: '" . esc_js($action) . "'}).then(function(token) {
                            var recaptchaResponse = document.getElementById('g-recaptcha-response');
                            if (recaptchaResponse) {
                                recaptchaResponse.value = token;
                            }
                        });
                    });
                });
            </script>";
        }
    }

    /**
     * Output reCAPTCHA widget for comment form (v2 or v3)
     */
    private function render_comment_recaptcha_widget()
    {
        $site_key = get_option('wp_recaptcha_site_key');
        $version = get_option('wp_recaptcha_version', 'v2');
        $site_key_js = esc_js($site_key);

        if ($version === 'v2') {
            echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
        } elseif ($version === 'v3') {
            echo '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">';
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var form = document.querySelector('form#commentform');
                    var submitButton = form ? form.querySelector('input[type=\"submit\"], button[type=\"submit\"]') : null;

                    if (form && submitButton) {
                        form.addEventListener('submit', function(event) {
                            if (document.getElementById('g-recaptcha-response').value === '') {
                                event.preventDefault();
                                submitButton.disabled = true;
                                submitButton.value = 'Verifying...';
                                grecaptcha.ready(function() {
                                    grecaptcha.execute('{$site_key_js}', {action: 'comment'}).then(function(token) {
                                        var recaptchaResponse = document.getElementById('g-recaptcha-response');
                                        if (recaptchaResponse) {
                                            recaptchaResponse.value = token;
                                        }
                                        submitButton.disabled = false;
                                        submitButton.value = 'Post Comment';
                                        form.submit();
                                    }).catch(function(error) {
                                        console.error('reCAPTCHA error:', error);
                                        submitButton.disabled = false;
                                        submitButton.value = 'Post Comment';
                                    });
                                });
                            }
                        });
                    }
                });
            </script>";
        }
    }

    /**
     * Common function to retrieve reCAPTCHA response from POST
     */
    private function get_recaptcha_response()
    {
        return isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';
    }

    /**
     * Validate the reCAPTCHA response
     */
    private function is_valid_captcha_response($captcha, $expected_action = 'submit')
    {
        $secret_key = get_option('wp_recaptcha_secret_key');
        $version = get_option('wp_recaptcha_version', 'v2');
		$threshold = get_option('wp_recaptcha_v3_threshold', 0.5);

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $captcha,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($result['success'])) {
            return false;
        }

        if ($version === 'v3') {
            if (empty($result['action']) || $result['action'] !== $expected_action) {
                return false;
            }
            if (empty($result['score']) || $result['score'] < floatval($threshold)) {
                return false;
            }
        }

        return true;
    }

    // ------------------------- LOGIN -------------------------

    public function add_login_recaptcha()
    {
        $this->enqueue_recaptcha_script();
        $this->print_recaptcha_inline_styles();
        $this->render_recaptcha_widget('login');
    }

    public function validate_login_recaptcha($user, $password)
    {
        $captcha = $this->get_recaptcha_response();
        if (empty($captcha)) {
            return new WP_Error('captcha_blank', __('<strong>ERROR</strong>: CAPTCHA is blank, please retry.'));
        }
        if (!$this->is_valid_captcha_response($captcha, 'login')) {
            return new WP_Error('captcha_error', __('<strong>ERROR</strong>: CAPTCHA validation failed, please try again.'));
        }
        return $user;
    }

    // ------------------------- REGISTRATION -------------------------

    public function add_registration_recaptcha()
    {
        $this->enqueue_recaptcha_script();
        $this->print_recaptcha_inline_styles();
        $this->render_recaptcha_widget('register');
    }

    public function validate_registration_recaptcha($errors, $sanitized_user_login, $user_email)
    {
        $captcha = $this->get_recaptcha_response();
        if (empty($captcha)) {
            $errors->add('captcha_blank', __('<strong>ERROR</strong>: CAPTCHA is blank, please retry.'));
        } elseif (!$this->is_valid_captcha_response($captcha, 'register')) {
            $errors->add('captcha_error', __('<strong>ERROR</strong>: CAPTCHA validation failed, please try again.'));
        }
        return $errors;
    }

    // ------------------------- PASSWORD RESET -------------------------

    public function add_lostpassword_recaptcha()
    {
        $this->enqueue_recaptcha_script();
        $this->print_recaptcha_inline_styles();
        $this->render_recaptcha_widget('lostpassword');
    }

    public function validate_lostpassword_recaptcha($errors, $user_data)
    {
        $captcha = $this->get_recaptcha_response();
        if (empty($captcha)) {
            $errors->add('captcha_blank', __('<strong>ERROR</strong>: CAPTCHA is blank, please retry.'));
        } elseif (!$this->is_valid_captcha_response($captcha, 'lostpassword')) {
            $errors->add('captcha_error', __('<strong>ERROR</strong>: CAPTCHA validation failed, please try again.'));
        }
        return $errors;
    }

    public function validate_password_reset_captcha($errors, $user)
    {
        $captcha = $this->get_recaptcha_response();
        if (empty($captcha)) {
            $errors->add('captcha_blank', __('<strong>ERROR</strong>: CAPTCHA is blank, please retry.'));
        } elseif (!$this->is_valid_captcha_response($captcha, 'resetpass')) {
            $errors->add('captcha_error', __('<strong>ERROR</strong>: CAPTCHA validation failed, please try again.'));
        }
        return $errors;
    }

    // ------------------------- COMMENTS -------------------------

    public function add_comment_recaptcha($defaults)
    {
        ob_start();
        $this->render_comment_recaptcha_widget();
        $recaptcha_html = ob_get_clean();

        $defaults['submit_field'] = $recaptcha_html . $defaults['submit_field'];
        return $defaults;
    }

    public function validate_comment_recaptcha($comment_data)
    {
        $captcha = $this->get_recaptcha_response();
        if (empty($captcha)) {
            wp_die(__('<b>Error:</b> Please confirm you are not a robot by clicking the reCAPTCHA box. <p><a href="javascript:history.back()">« Back</a>', 'text-domain'));
        }
        if (!$this->is_valid_captcha_response($captcha, 'comment')) {
            wp_die(__('<b>Error:</b> reCAPTCHA verification failed. Please try again or contact the site admin if the problem persists.', 'text-domain'));
        }
        return $comment_data;
    }

    // ------------------------- CLEANUP -------------------------

    public function remove_comment_author_url($author_link)
    {
        $comment = get_comment();
        return get_comment_author($comment);
    }

    public function remove_urls_from_comment_content($comment_data)
    {
        if (isset($comment_data['comment_content'])) {
            $comment_data['comment_content'] = preg_replace('#<a.*?>(.*?)</a>#i', '$1', $comment_data['comment_content']);
            $comment_data['comment_content'] = preg_replace('#\b(https?|ftp|ftps)://[^\s]+#i', '', $comment_data['comment_content']);
            $comment_data['comment_content'] = preg_replace('#\b(www\.[^\s]+)#i', '', $comment_data['comment_content']);
        }
        return $comment_data;
    }
}
