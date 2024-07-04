<?php

/**
 * Google reCAPTCHA in WordPress Comments & Login Form
 */
class GoogleReCaptcha
{

    private $site_key;
    private $secret_key;

    // Class constructor
    public function __construct($site_key, $secret_key)
    {
        $this->site_key = $site_key;
        $this->secret_key = $secret_key;

        // Only hook these actions and filters if the user is not logged in
        if (!is_user_logged_in()) {

            add_action('login_form', [$this, 'add_login_recaptcha']);
            add_filter('wp_authenticate_user', [$this, 'validate_login_recaptcha'], 10, 2);


            add_filter('comment_form_defaults', [$this, 'add_google_recaptcha']);
            add_action('pre_comment_on_post', [$this, 'verify_google_recaptcha']);
            
          	add_filter('get_comment_author_link', [$this, 'remove_comment_author_url']);
        }

    }

    # enqueue scripts is not used to avoid adding on all unnecessary pages
    public function add_recaptcha_scripts() {
        echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        echo '<style>
            .g-recaptcha {
                margin-bottom: 20px;
                transform: scale(0.89);
                transform-origin: 0 0;
            }
          </style>';
    }


    // Method to add CAPTCHA to login form
    public function add_login_recaptcha()
    {

        $site_key = $this->site_key;
        $recaptcha_html = '<div class="g-recaptcha" data-sitekey="' . $site_key . '"></div>';
        echo $recaptcha_html;
        // Script to load the reCAPTCHA JavaScript API
        $this->add_recaptcha_scripts();
    }

    // Method to validate CAPTCHA on login
    public function validate_login_recaptcha($user, $password)
    {
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
            // Perform the CAPTCHA validation
            $response = $this->is_valid_captcha_response($_POST['g-recaptcha-response']);
            if (!$response) {
                return new WP_Error('captcha_error', __('<strong>ERROR</strong>: CAPTCHA validation failed, please try again.'));
            }
        } else {
            return new WP_Error('captcha_blank', __('<strong>ERROR</strong>: CAPTCHA is blank, please retry.'));
        }
        return $user;
    }


    /**
     * Add the reCAPTCHA widget before the submit button in the comment form.
     */
    public function add_google_recaptcha($submit_field)
    {
        $site_key = $this->site_key;
        $recaptcha_html = '<div class="g-recaptcha" data-sitekey="' . $site_key . '"></div>';
        // Script to load the reCAPTCHA JavaScript API
        $this->add_recaptcha_scripts();
        $submit_field['submit_field'] = $recaptcha_html . $submit_field['submit_field'];
        return $submit_field;
    }

    /**
     * Verify the reCAPTCHA response.
     */
    private function is_valid_captcha_response($captcha)
    {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $this->secret_key,
                'response' => $captcha,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        return !empty($result['success']);
    }

    /**
     * Validate comment submission for correct CAPTCHA.
     */
    public function verify_google_recaptcha($comment_data)
    {
        $visitor_hash = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']); // Create a unique hash for the visitor.
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? esc_attr($_POST['g-recaptcha-response']) : '';
        if (empty($recaptcha_response)) {
            wp_die(__('<b>Error:</b> Please confirm you are not a robot by clicking the reCAPTCHA box. <p><a href="javascript:history.back()">« Back</a>', 'text-domain'));
            exit;
        } elseif (!$this->is_valid_captcha_response($recaptcha_response)) {
            wp_die(__('<b>Error:</b> reCAPTCHA verification failed. Please try again or contact the site admin if the problem persists.', 'text-domain'));
            exit;
        }
        return $comment_data;
    }

    public function remove_comment_author_url($author_link) {
        $comment = get_comment();
        $author = get_comment_author($comment);
        return $author;
    }
}

