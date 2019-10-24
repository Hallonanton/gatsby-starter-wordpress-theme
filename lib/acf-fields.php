<?php
/*==============================================================================

  # Add options pages
  # Enqueue styles for ACF-adminpage
  # Add acf_nullify_empty if it dosen't exists
  # Add generated code

==============================================================================*/

/*==============================================================================
  # Add options pages
==============================================================================*/

//Remove standard acf-options-page
add_action('admin_init', 'remove_acf_options_page', 99);
function remove_acf_options_page() {
	remove_menu_page('acf-options');
}

function add_custom_acf_options_page(){   
	if( function_exists( 'acf_add_options_page' ) ){
		acf_add_options_page(array(
			'page_title'   => 'Generella fält',
			'menu_title'   => 'Generella fält',
			'menu_slug'    => 'general_fields',
			'capability'   => 'edit_posts',
			'parent_slug'  => '',
			'position'     => '8',
			'icon_url'     => false,
			'redirect'     => true,
		));
	}
}
add_action( 'init', 'add_custom_acf_options_page' );


/*==============================================================================
  # Add acf_nullify_empty if it dosen't exists
==============================================================================*/

if (!function_exists('acf_nullify_empty')) {
  /**
   * Return `null` if an empty value is returned from ACF.
   *
   * @param mixed $value
   * @param mixed $post_id
   * @param array $field
   *
   * @return mixed
   */
  function acf_nullify_empty($value, $post_id, $field) {

    if (empty($value)) {
      return null;
    }

    return $value;
  }
}

add_filter('acf/format_value', 'acf_nullify_empty', 100, 3);


/*==============================================================================
  # Enqueue styles for ACF-adminpage
==============================================================================*/

function my_acf_admin_enqueue_scripts() {
	wp_register_style( 'acf-admin-css', get_stylesheet_directory_uri() . '/assets/styles/acf-admin.css', false, '1.0.0' );
	wp_enqueue_style( 'acf-admin-css' );
}
add_action( 'acf/input/admin_enqueue_scripts', 'my_acf_admin_enqueue_scripts' );