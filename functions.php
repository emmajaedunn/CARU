<?php

/**
 * Simple Nova Theme Functions
 * 
 * @package Simple-Nova
 */

// Require TGMPA for plugin activation
require_once get_template_directory() . '/inc/tgm/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'simple_nova_register_required_plugins', 0);
function simple_nova_register_required_plugins()
{
    $plugins = array(
        array(
            'name'      => 'Superb Addons',
            'slug'      => 'superb-blocks',
            'required'  => false,
        ),
    );

    $config = array(
        'id'           => 'simple-nova',
        'default_path' => '',
        'menu'         => 'tgmpa-install-plugins',
        'has_notices'  => true,
        'dismissable'  => true,
        'dismiss_msg'  => '',
        'is_automatic' => true,
        'message'      => '',
    );

    tgmpa($plugins, $config);
}

// Enqueue theme styles
function simple_nova_pattern_styles()
{
    wp_enqueue_style('simple-nova-patterns', get_template_directory_uri() . '/assets/css/patterns.css', array(), filemtime(get_template_directory() . '/assets/css/patterns.css'));
    if (is_admin()) {
        global $pagenow;
        if ('site-editor.php' === $pagenow) return;
        wp_enqueue_style('simple-nova-editor', get_template_directory_uri() . '/assets/css/editor.css', array(), filemtime(get_template_directory() . '/assets/css/editor.css'));
    }
}
add_action('enqueue_block_assets', 'simple_nova_pattern_styles');

add_theme_support('wp-block-styles');
add_action('init', function () { remove_theme_support('core-block-patterns'); });

// Register Simple Nova block pattern categories
function simple_nova_register_block_pattern_categories()
{
    $categories = array(
        'heros' => 'Heros',
        'navigation_headers' => 'Headers',
        'teams' => 'Teams',
        'testimonials' => 'Testimonials',
        'contact' => 'Contact'
    );

    foreach ($categories as $slug => $label) {
        register_block_pattern_category($slug, array(
            'label' => __($label, 'simple-nova'),
            'description' => __("Simple Nova $label patterns", 'simple-nova')
        ));
    }
}
add_action('init', 'simple_nova_register_block_pattern_categories');

// Initialize theme info content
require_once trailingslashit(get_template_directory()) . 'inc/vendor/autoload.php';
use SuperbThemesThemeInformationContent\ThemeEntryPoint;
add_action('init', function () {
    ThemeEntryPoint::init([
        'type' => 'block',
        'theme_url' => 'https://superbthemes.com/simple-nova/',
        'demo_url' => 'https://superbthemes.com/demo/simple-nova/',
        'features' => array(
            array('title'=>"Theme Designer",'icon'=>"lego-duotone.webp",'description'=>"Choose from over 300 designs for footers, headers, landing pages & all other theme parts."),
            array('title'=>"Editor Enhancements",'icon'=>"1-1.png",'description'=>"Enhanced editor experience, grid systems, improved block control and much more."),
            array('title'=>"Custom CSS",'icon'=>"2-1.png",'description'=>"Add custom CSS with syntax highlight, custom display settings, and minified output."),
            array('title'=>"Animations",'icon'=>"wave-triangle-duotone.webp",'description'=>"Animate any element on your website with one click. Choose from over 50+ animations."),
            array('title'=>"WooCommerce Integration",'icon'=>"shopping-cart-duotone.webp",'description'=>"Choose from over 100 unique WooCommerce designs for your e-commerce store."),
            array('title'=>"Responsive Controls",'icon'=>"arrows-out-line-horizontal-duotone.webp",'description'=>"Make any theme mobile-friendly with SuperbThemes responsive controls.")
        )
    ]);
});

// ---------------- WooCommerce Registration/Login Simplified ---------------- //

// Enable registration on My Account page
add_filter('woocommerce_registration_enabled', '__return_true');

// Add only First Name and Last Name to registration form
add_action('woocommerce_register_form_start', 'caru_add_name_fields');
function caru_add_name_fields() {
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_first_name"><?php esc_html_e('First Name', 'woocommerce'); ?> <span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="reg_first_name" value="<?php if (!empty($_POST['first_name'])) echo esc_attr($_POST['first_name']); ?>" />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_last_name"><?php esc_html_e('Last Name', 'woocommerce'); ?> <span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="reg_last_name" value="<?php if (!empty($_POST['last_name'])) echo esc_attr($_POST['last_name']); ?>" />
    </p>
    <?php
}

// Validate the fields
add_filter('woocommerce_registration_errors', 'caru_validate_name_fields', 10, 3);
function caru_validate_name_fields($errors, $username, $email) {
    if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('First name is required!', 'woocommerce'));
    }
    if (empty($_POST['last_name'])) {
        $errors->add('last_name_error', __('Last name is required!', 'woocommerce'));
    }
    return $errors;
}

// Save the fields
add_action('woocommerce_created_customer', 'caru_save_name_fields');
function caru_save_name_fields($customer_id) {
    if (isset($_POST['first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (isset($_POST['last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
}

// ---------------- REMOVE "Don't have an account? Register" link ---------------- //
remove_action('woocommerce_before_customer_login_form', 'custom_login_register_message');

// Disable WooCommerce "new account" email notification (optional)
add_filter('woocommerce_new_customer_notification_email', '__return_false');