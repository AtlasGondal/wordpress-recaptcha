## WordPress ReCaptcha Plugin

The **WordPress reCAPTCHA** plugin protects your site from spam, abuse, and bots by seamlessly integrating Google reCAPTCHA (v2 and v3) into essential WordPress forms. Lightweight, fast, and flexible — built to keep your website safe without slowing it down.

### Features

- Adds **Google reCAPTCHA v2** (checkbox) or **v3** (invisible score-based) to:
  - WordPress **Login Form**
  - **Registration Form**
  - **Password Reset / Lost Password Form**
  - **Comment Submission Form**
- **v3 Score Threshold Setting**: Configure your minimum acceptance score (default 0.5) for invisible reCAPTCHA v3.
- **Mask reCAPTCHA Keys** in admin settings for improved security.
- **Removes URLs** automatically from comment authors and comment content to prevent spammy links.
- **Optimized Script Loading**: reCAPTCHA scripts are only loaded where necessary (not globally).
- **Customizable Admin Settings Page**: Modern, clean interface with options for reCAPTCHA version and v3 threshold control.
- Easy setup: just enter your Google reCAPTCHA Site Key and Secret Key.
- Developer-friendly, lightweight, no bloat.

### Usage

Once the plugin is activated and configured:

- A reCAPTCHA box (for v2) or invisible background check (for v3) will be added automatically to your WordPress login, registration, password reset, and comment forms.
- For v2, users complete a visible reCAPTCHA checkbox challenge.
- For v3, the plugin scores user interactions invisibly and blocks low-confidence (suspicious) submissions.
- All submissions are verified server-side with Google reCAPTCHA for maximum protection.

### Customization

- **Choose reCAPTCHA Version**: Easily switch between v2 (checkbox) and v3 (invisible) in plugin settings.
- **Set v3 Score Threshold**: Adjust the minimum score required to allow a submission (default 0.5).
- **Mask Site and Secret Keys**: View protected keys in the admin area for added security.


### Installation

1. Download the plugin from the release page.
2. Go to Plugins page, and click on Add New
3. Select Upload, and choose `wordpress-recaptcha.zip` file
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Navigate to the plugin settings under `Settings > Wordpress ReCaptcha` in the WordPress admin area.
6. Enter your Google reCAPTCHA site key and secret key. If you don't have these keys, [register your site with Google reCAPTCHA](https://www.google.com/recaptcha/about/) to get them.

### Customization

Currently, the plugin automatically applies Google reCAPTCHA to the login and comment forms without additional customization options. Future versions may include more customizable features.

### Troubleshooting

If you encounter any issues while using the plugin, please ensure that:

- You have entered the correct site key and secret key in the plugin settings.
- Your server is able to communicate with the Google reCAPTCHA API servers.
- You have not installed other plugins that may interfere with reCAPTCHA validation.

### Already Implemented

- ✅ reCAPTCHA protection for **Login**, **Registration**, **Password Reset**, and **Comments**.
- ✅ **v2 and v3** full support.
- ✅ **Threshold configuration** for v3.
- ✅ **Script optimization** for faster page loading.
- ✅ **Spam link removal** from comment content.

### To-Do (Future Enhancements)

1. **Integration with Popular Plugins:**
   - **Contact Form 7**
   - **WooCommerce**
   - **Other popular form builders**

2. **More Admin Controls:**
   - Enable/disable reCAPTCHA per form individually.
   - Per-role (user role) reCAPTCHA control.

  
I am actively working on these features and welcome contributions from the community to expedite their development. If you would like to contribute or have suggestions for other features, please visit my GitHub repository or contact me directly through my [website](hhttps://atlasgondal.com/contact-me/?utm_source=self&utm_medium=wp&utm_campaign=wordpress-recaptcha&utm_term=readme).


#### Contributions

Contributions to the WordPress ReCaptcha plugin are welcome. Please visit the plugin's GitHub repository to submit issues or pull requests.

#### License

The WordPress ReCaptcha plugin is open-source software licensed under the GPL v2.0 license. For more information, please see the LICENSE file or visit the GNU website.
