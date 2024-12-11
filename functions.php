<?php
	// add theme support
	add_theme_support( 'post-thumbnails' );

	// add custom logo support
	add_theme_support('custom-logo');

	// Add CORS support
	function add_cors_http_header() {
		header("Access-Control-Allow-Origin: *");
	}
	add_action('init', 'add_cors_http_header');

	// Enqueue parent and custom styles
	function enqueue_parent_and_custom_styles() {
		wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
		wp_enqueue_style('child-style', get_template_directory_uri() . '/custom.css', 	array('parent-style'));
	}
	add_action('wp_enqueue_scripts', 'enqueue_parent_and_custom_styles');

	// custom excerpt length 
	function custom_excerpt_length($length) {
		return 10; 
	}

	// Hook the function to the 'excerpt_length' filter
	add_filter('excerpt_length', 'custom_excerpt_length', 999);

	// disable automatic <p> tags for product descriptions
	remove_filter('the_content', 'wpautop');
	remove_filter('woocommerce_short_description', 'wpautop');

	// split article rendered content
	function split_article_content($atts, $content = null) {
    if (!$content) return '';

    // Use DOMDocument to split the content
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    // Extract <h4> and <p>
    $headings = $dom->getElementsByTagName('h4');
    $paragraphs = $dom->getElementsByTagName('p');

    $output = '';

    // Append the first <h4>
    if ($headings->length > 0) {
        $output .= '<h4>' . $headings->item(0)->nodeValue . '</h4>';
    }

    // Append the first <p>
    if ($paragraphs->length > 0) {
        $output .= '<p>' . $paragraphs->item(0)->nodeValue . '</p>';
    }

    return $output;
	}
	add_shortcode('split_content', 'split_article_content');

	// Customizer settings
	function custom_theme_customize_register($wp_customize) {
		// Register and customizer settings
		$wp_customize->add_setting('background_color', array(
				'default' => '#ffffff',
				'transport' => 'postMessage',
		));

		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'background_color', array(
				'label' => __('Background Colour', 'custom-theme'),
				'section' => 'colors',
		)));

		// Font Family Section
		$wp_customize->add_section('fonts', array(
				'title' => __('Fonts', 'custom-theme'),
				'priority' => 30,
		));

		// Font Family Setting
		$wp_customize->add_setting('font_family', array(
				'default' => 'Palanquin',
				'transport' => 'postMessage',
		));

		// Font Family Control
		$wp_customize->add_control('font_family_control', array(
				'label' => 'Font Family',
				'section' => 'fonts',
				'settings' => 'font_family',
				'type' => 'select',
				'choices' => array(
						'Palanquin' => 'Palanquin',
						'Radio Canada' => 'Radio Canada',
						'Noto Sans' => 'Noto Sans',
						'Hind' => 'Hind',
						'Fredoka' => 'Fredoka',
				),
		));

		// Navbar Background Color
		$wp_customize->add_setting('navbar_color', array(
				'default' => '#5AC1C8',
				'transport' => 'postMessage',
		));

		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'navbar_color', array (
				'label' => __('Navbar Colour', 'custom-theme'),
				'section' => 'colors',
		)));

		// Add setting for mobile navbar color
		$wp_customize->add_setting('mobile_menu_color', array(
			'default' => '#5AC1C8',
			'transport' => 'postMessage',
		));

		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'mobile_menu_color', array(
			'label' => __('Mobile Navbar Colour', 'custom-theme'),
			'section' => 'colors',
		)));

	}

	add_action('customize_register', 'custom_theme_customize_register');

	// Custom Rest API endpoint to retrieve customizer settings
	add_action('rest_api_init', function () {
		register_rest_route('custom-theme/v1', '/customizer-settings', array(
				'methods' => 'GET',
				'callback' => 'get_customizer_settings',
				'permission_callback' => '__return_true', // Public endpoint
		));
	});

	// Customizer settings callback
	function get_customizer_settings() {
		$settings = array(
				'backgroundColor' => get_theme_mod('background_color', '#ffffff'),
				'fontFamily' => get_theme_mod('font_family', 'Arial'),
				'mobileMenuColor' => get_theme_mod('mobile_menu_color', '#ffffff'),
				'navbarColor' => get_theme_mod('navbar_color', '#ffffff'),
		);

		return rest_ensure_response($settings);
	}

?>