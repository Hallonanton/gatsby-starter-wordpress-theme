<?php
/*==============================================================================
	
  # Handle images
  # Change max upload size
  # Google maps API
  # Message on wp dashboard
  # Clean up the admin menu
  # Disabling the Gutenberg
  # Always redirect to wp-admin
  # Rewrite/Redirect categoreis/taxonomies
  # Change wp-permalinks to gatsby-permalinks
  # Rewrite permalinks for categories
  # Register menu
  # Move Yoast meta to bottom
  # populate_post_obj_with_acf
  # Add more data to posts for use with ACF Post_obj
  # Add yoast meta to rest api
  # Add categories to cpt rest api
  # Add default excerpt to posts
  # Add custom post-state for dummy-template
  # Remove some flexible content layouts from acf on different templates/post types
  # Customize TinyMCE
  # Attachment filter
  # Add page-attribute support for posts
  # Add deploy page

==============================================================================*/

/*==============================================================================
  # Handle images
==============================================================================*/

add_filter('jpeg_quality', function($arg){ return 100; });


/*==============================================================================
  # Change max upload size
==============================================================================*/

@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );
define( 'ALLOW_UNFILTERED_UPLOADS', true );


/*==============================================================================
  # Google maps API
==============================================================================*/

function my_acf_init() {
  $google_maps_key = get_field( 'google_maps_key', 'options' );
  acf_update_setting('google_api_key', $google_maps_key );
}
add_action('acf/init', 'my_acf_init');


/*==============================================================================
  # Message on wp dashboard
==============================================================================*/

add_action('wp_dashboard_setup', 'gatsby_dashboard_widget');
  
function gatsby_dashboard_widget() {
	global $wp_meta_boxes;
	wp_add_dashboard_widget('custom_help_widget', 'Gatsby + Wordpress', 'custom_dashboard_gastby_reminder');
}
 
function custom_dashboard_gastby_reminder() {
echo '<p>Detta temat stödjer ingen egen front-end. Temat är byggt som ett CMS till <a href="https://www.gatsbyjs.org/" target="_blank">Gatsby.js</a>. Detta wordpress hanterar alltså enbart den data som visas på sidan men koden i sig är byggd i React med hjälp av Gatsby</p>';
}


/*==============================================================================
  # Clean up the admin menu
==============================================================================*/

//Remove these pages from the menu so that the user has a easier time finding whats important
add_action( 'admin_menu', 'custom_menu_page_removing' );

function custom_menu_page_removing() { 
  remove_menu_page( 'edit-comments.php' );    //Comments
  remove_menu_page( 'edit.php' );           //Posts
}

add_action( 'admin_menu', 'adjust_the_wp_menu', 999 );
function adjust_the_wp_menu() {
  remove_submenu_page( 'themes.php', 'theme-editor.php' );
  remove_submenu_page( 'themes.php', 'widgets.php' );
  remove_submenu_page( 'themes.php', 'customize.php' );
  //remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
  remove_submenu_page( 'tools.php', 'export_personal_data' );
  remove_submenu_page( 'tools.php', 'remove_personal_data' );
  remove_submenu_page( 'options-general.php', 'options-writing.php' );
  remove_submenu_page( 'options-general.php', 'options-discussion.php' );
  remove_submenu_page( 'options-general.php', 'options-media.php' );
  //remove_submenu_page( 'options-general.php', 'privacy.php' );
}


/*==============================================================================
  # Disabling the Gutenberg
==============================================================================*/

function disable_gutenberg_post($can_edit, $post) {
  return false;
}
add_filter('use_block_editor_for_post', 'disable_gutenberg_post', 10, 2);


/*==============================================================================
  # Always redirect to wp-admin
==============================================================================*/

function restrict_wp_content() {

	if( !is_admin() ) {

		$wp_admin = admin_url();

		wp_redirect( $wp_admin, 301 ); 
    exit;
	}

}
add_action( 'template_redirect', 'restrict_wp_content' );



/*==============================================================================
  # Change wp-permalinks to gatsby-permalinks
==============================================================================*/

function change_wp_link_to_gatsby( $url ) {

  $wordpress_url = get_site_url();
  $frontend_url = rtrim( get_field('frontend_url', 'options'), '/');
  $url = str_replace($wordpress_url, $frontend_url, $url);

  return $url;
}


add_filter( 'post_link', 'change_wp_link_to_gatsby', 99, 1 );
add_filter( 'page_link', 'change_wp_link_to_gatsby', 99, 1 );
add_filter( 'post_type_link', 'change_wp_link_to_gatsby', 99, 1 );
add_filter( 'post_type_archive_link', 'change_wp_link_to_gatsby', 99, 1 );
add_filter( 'term_link', 'change_wp_link_to_gatsby', 99, 1);
add_filter( 'attachment_link', 'change_wp_link_to_gatsby', 99, 1 );


/*==============================================================================
  # Rewrite/Redirect categoreis/taxonomies
==============================================================================*/

/*
 * Set up variables
 */
//Add name on affected taxonomies
global $watch_taxonomies;

//$key equals old path, $value equals new path
$watch_taxonomies = [
  'case_category'   => 'case',
  'inspo_category'  => 'inspo',
  'category'        => 'post'
];



/*
 * Helper function to determine primary term for posts
 */
function determine_primary_term_slug_path( $post_id, $tax_slug ) {

  $primary_term = null;
  $term_slug_path = '';
  $terms = wp_get_post_terms( $post_id, $tax_slug );


  //If yoast-seo is installed, get the primary term from WPSEO_Primary_Term
  if ( class_exists( 'WPSEO_Primary_Term' ) ) :

    $set_primary_term = new WPSEO_Primary_Term( $tax_slug, $post_id );
    $set_primary_term = $set_primary_term->get_primary_term();

    if ( $set_primary_term ) :

      $primary_term = get_term( $set_primary_term, $tax_slug );

    endif;

  endif;

  //If yoast-seo isn't installed, set the first term as primary
  if ( $terms && !$primary_term ) :

    $primary_term = $terms[0];

  endif;

  //Continue if a primary term is found
  if ( $primary_term && !isset( $primary_term->errors['invalid_term'] ) ) : 

    $base_term = $primary_term;
    $term_slug_path = $base_term->slug;

    while( $base_term->parent ) :

      $base_term = get_term( $base_term->parent, $tax_slug );
      $term_slug_path = $base_term->slug.'/'.$term_slug_path;

    endwhile;

    $term_slug_path = '/'.$term_slug_path;

  endif;


  return $term_slug_path;
}




/*
 * Filter-function that handles the rewrite of $watch_taxonomies
 */
function add_custom_tax_post_rewrite( $rules, $is_tax_filter = false ) {

  //$rules is only used if the function is called by rewrite_rules_array
  //If the function is called by edited_terms or created_term $rules = $term_id and $is_tax_filter = $taxonomy
  //If the function is called by transition_post_status $rules = $new_status and $is_tax_filter = $old_status
  //Therfore $is_tax_filter will be true if called by any other filter than rewrite_rules_array

  global $wp_rewrite;
  global $watch_taxonomies;
  $rewrites = [];


  //Rewrite for terms
  foreach ( $watch_taxonomies as $tax_slug => $post_type ) :

    $cpt_obj = get_post_type_object( $post_type );
    $cpt_slug = ( $post_type === 'post' ) ? 'press' : $cpt_obj->rewrite['slug'];


    $terms = get_terms( $tax_slug, [
      'hide_empty' => false,
    ]);

    if ( $terms ) :
      foreach ( $terms as $term_obj ) : 

        $term_slug = $term_obj->slug;
        $term_parent = $term_obj->parent;

        while ( $term_parent ) :
          $parent_obj = get_term_by( 'id', $term_parent, $tax_slug );
          $term_slug = $parent_obj->slug.'/'.$term_slug;
          $term_parent = $parent_obj->parent;
        endwhile;

        $regex_rule = '^'.$cpt_slug.'/'.$term_slug.'?$';
        $redirect = 'index.php?'.$tax_slug.'='.$term_obj->slug;

        //Add rewrite rule in array for later use
        $rewrites[$regex_rule] = $redirect;

      endforeach;
    endif; 
    
  endforeach; 


  //Save rules
  if ( $is_tax_filter ) : 

    //Add rule with add_rewrite_rule if action edited_terms or created_term
    foreach ( $rewrites as $regex_rule => $redirect ) :
      add_rewrite_rule( $regex_rule, $redirect, 'top' );
    endforeach;

    $wp_rewrite->flush_rules();

  else :

    //Add rules with $rules if is filter rewrite_rules_array
    $rules = array_reverse( $rules, true );

    foreach ( $rewrites as $regex_rule => $redirect ) :
      $rules[$regex_rule] = $redirect;
    endforeach;

    $rules = array_reverse( $rules, true );

    return $rules;

  endif;
}

//Called when new terms are created or a term is updated
add_action( 'edited_terms', 'add_custom_tax_post_rewrite', 10, 2 );
add_action( 'created_term', 'add_custom_tax_post_rewrite', 10, 2 );

//Called when a post is published or changed
add_action( 'transition_post_status', 'add_custom_tax_post_rewrite', 10, 2 );


//Called when wp is about too alter the rewrite_rules_array
add_filter( 'rewrite_rules_array', 'add_custom_tax_post_rewrite' );




/*
 * Filter-function that changes the term_link for all terms connected to $watch_taxonomies
 */
function change_rewrited_terms_link( $url, $term_obj, $tax_slug ) {

  global $watch_taxonomies;

  if ( array_key_exists( $tax_slug, $watch_taxonomies ) ) {
    
    $post_type = $watch_taxonomies[$tax_slug];
    $cpt_obj = get_post_type_object( $post_type );
    $cpt_slug = ( $tax_slug === 'category' ) ? 'press' : $cpt_obj->rewrite['slug'];

    $term_slug = $term_obj->slug;
    $term_parent = $term_obj->parent;

    while ( $term_parent ) :
      $parent_obj = get_term_by( 'id', $term_parent, $tax_slug );
      $term_slug = $parent_obj->slug.'/'.$term_slug;
      $term_parent = $parent_obj->parent;
    endwhile;

    $new_path = '/'.$cpt_slug.'/'.$term_slug.'/'.$getparam;
    $url = get_home_url().$new_path;

  }

  return $url;
}

add_filter( 'term_link', 'change_rewrited_terms_link', 10, 3 );


/*==============================================================================
  # Register menu
==============================================================================*/

function gatsby_register_menu() {
  register_nav_menu('primary',__( 'Primär' ));
}

add_action( 'init', 'gatsby_register_menu' );


/*==============================================================================
  # Move Yoast meta to bottom
==============================================================================*/

function yoasttobottom() {
  return 'low';
}
add_filter( 'wpseo_metabox_prio', 'yoasttobottom');


/*==============================================================================
  # populate_post_obj_with_acf
==============================================================================*/

function populate_post_obj_with_acf( $posts, $post_type = 'post' ) {

  $fields_to_check = [];

  switch ( $post_type ) {
    case 'case':
      $fields_to_check[] = 'label';
      $fields_to_check[] = 'excerpt_main_image';
      $fields_to_check[] = 'main_image';
      $fields_to_check[] = 'horizontal_image';
      $fields_to_check[] = 'align_bg_x';
      $fields_to_check[] = 'align_bg_y';

      break;

    case 'coworkers':
      $fields_to_check[] = 'label';
      $fields_to_check[] = 'mail';
      $fields_to_check[] = 'phone';
      $fields_to_check[] = 'about';
      $fields_to_check[] = 'main_image';
      $fields_to_check[] = 'vertical_image';
      $fields_to_check[] = 'cpt_categories';

      break;
    
    default:
      $fields_to_check[] = 'heading';
      $fields_to_check[] = 'excerpt';
      $fields_to_check[] = 'excerpt_main_image';
      $fields_to_check[] = 'main_image';

      break;
  }

  if ( $posts ) :
    foreach( $posts as $key => $post ) :
      if ( $posts[$key] ) : 

        $posts[$key]->link = get_permalink($post->ID);
        $posts[$key]->acf = [];

        if ( $fields_to_check ) : 
          foreach( $fields_to_check as $field) : 
            if ( $value = get_field($field, $post->ID) ) :

              $posts[$key]->acf[$field] = $value;

            elseif ( $field === 'cpt_categories' ) :

              $posts[$key]->acf[$field] = add_categories_to_cpt_rest( $post );

            endif;
          endforeach;
        endif;
      endif;
    endforeach;
  endif;

  return $posts;
}



/*==============================================================================
  # Add more data to posts for use with ACF Post_obj
==============================================================================*/

add_filter( 'acf/rest_api/page/get_fields', function( $data, $request, $response ) {
  if ( $response instanceof WP_REST_Response ) {
      $data = $response->get_data();
  }

  //Add articles/cases etc as content 
  if ( $data['acf']['content'] ) :
    foreach( $data['acf']['content'] as $key => $layout ) :

      switch ($layout['acf_fc_layout']) {

        /*
         * Press
         */
        case 'Press':
          
          $press = null;

          $args = [
            'posts_per_page'  => 3,
            'post_type'       => 'post',
            'post_status'     => 'publish',
            'orderby'         => 'date',
            'order'           => 'DESC'
          ];

          if ( $layout['type'] === 'latest' ) :
            
            $press = get_posts( $args );

          elseif ( $layout['type'] === 'category' )  : 
            
            $args['tax_query'] = [
              [
                'taxonomy'    => 'category',
                'field'       => 'term_id',
                'terms'       => $layout['category']
              ]
            ];
            $press = get_posts( $args );

          elseif ( $layout['type'] === 'choosen' )  : 

            if ( $layout['choosen'] ) : 
              foreach ( $layout['choosen'] as $choosen ) : 

                $press[] = $choosen['press'];

              endforeach;
            endif;
            
          endif;

          $data['acf']['content'][$key]['posts'] = populate_post_obj_with_acf( $press, 'post' );
          break;


        /*
         * Inspo
         */
        case 'Inspo':
          
          $inspo = null;

          $args = [
            'posts_per_page'  => 3,
            'post_type'       => 'inspo',
            'post_status'     => 'publish',
            'orderby'         => 'date',
            'order'           => 'DESC'
          ];

          if ( $layout['type'] === 'latest' ) :
            
            $inspo = get_posts( $args );

          elseif ( $layout['type'] === 'category' )  : 
            
            $args['tax_query'] = [
              [
                'taxonomy'    => 'inspo_category',
                'field'       => 'term_id',
                'terms'       => $layout['category']
              ]
            ];
            $inspo = get_posts( $args );

          elseif ( $layout['type'] === 'choosen' )  : 

            if ( $layout['choosen'] ) : 
              foreach ( $layout['choosen'] as $choosen ) : 

                $inspo[] = $choosen['inspo'];

              endforeach;
            endif;
            
          endif;

          $data['acf']['content'][$key]['posts'] = populate_post_obj_with_acf( $inspo, 'inspo' );
          break;

        
        /*
         * CaseSlider
         */
        case 'CaseSlider':
          
          $cases = null;

          $args = [
            'posts_per_page'  => 3,
            'post_type'       => 'case',
            'post_status'     => 'publish',
            'orderby'         => 'date',
            'order'           => 'DESC'
          ];

          if ( $layout['type'] === 'latest' ) :
            
            $cases = get_posts( $args );

          elseif ( $layout['type'] === 'category' )  : 

            $args['tax_query'] = [
              [
                'taxonomy'    => 'case_category',
                'field'       => 'term_id',
                'terms'       => $layout['category']
              ]
            ];
            $cases = get_posts( $args );

          elseif ( $layout['type'] === 'choosen' )  : 

            if ( $layout['choosen'] ) : 
              foreach ( $layout['choosen'] as $choosen ) : 

                $cases[] = $choosen['case'];

              endforeach;
            endif;
            
          endif;

          $data['acf']['content'][$key]['cases'] = populate_post_obj_with_acf( $cases, 'case' );
          break;

        /*
         * Coworkers
         */
        case 'Coworkers':

          $coworkers = null;

          $args = [
            'posts_per_page'  => -1,
            'post_type'       => 'coworkers',
            'post_status'     => 'publish',
            'orderby'         => 'menu_order',
            'order'           => 'ASC'
          ];

          if ( $layout['type'] === 'simple' && $layout['category'] ) {
            $args['tax_query'] = [
              [
                'taxonomy' => 'coworkers_category',
                'field'    => 'term_id',
                'terms'    => $layout['category'],
              ]
            ];
          }

          $coworkers = get_posts( $args );
          $data['acf']['content'][$key]['coworkers'] = populate_post_obj_with_acf( $coworkers, 'coworkers' );

          //Add categories for filtering
          $terms = get_terms([
            'taxonomy' => 'coworkers_category',
            'hide_empty' => true,
            'orderby' => 'description',
            'order' => 'ASC',
          ]);

          if($terms) {
            $coworker_sections = [];

            foreach( $terms as $term ) :

              $coworker_sections[] = [
                'name' => $term->name,
                'slug' => $term->slug,
                'link' => get_term_link( $term, 'coworkers_category' )
              ];

            endforeach;

            $data['acf']['content'][$key]['coworkers_category'] = $coworker_sections;
          }

          break;
      }

    endforeach;
  endif;

  return $data;
}, 10, 3 );


/*==============================================================================
  # Add yoast meta to rest api
==============================================================================*/

function add_yoast_meta_to_rest( $post ) {

  $yoast_meta = [
    'yoast_wpseo_title' => get_post_meta( $post['id'], '_yoast_wpseo_title', true),
    'yoast_wpseo_metadesc' => get_post_meta( $post['id'], '_yoast_wpseo_metadesc', true),
    'yoast_wpseo_canonical' => get_post_meta( $post['id'], '_yoast_wpseo_canonical', true)
  ];

  return $yoast_meta;
}
 
function create_api_yoast_meta() {

  $yoast_to_rest_types = [
    'post',
    'page',
    'case',
    'inspo',
    'category',
    'coworkers',
    'case_category',
    'inspo_category'
  ];

  foreach( $yoast_to_rest_types as $type ) : 

    register_rest_field( $type,
      'yoast_meta',
      array(
       'get_callback'    => 'add_yoast_meta_to_rest',
       'update_callback' => null,
       'schema'          => null,
      )
    );

  endforeach;
}
 
add_action( 'rest_api_init', 'create_api_yoast_meta' );


/*==============================================================================
  # Add categories to cpt rest api
==============================================================================*/

function add_categories_to_cpt_rest( $post ) {

  $post_type = ( gettype($post) === 'array' ) ? $post['type'] : $post->post_type;
  $post_ID = ( gettype($post) === 'array' ) ? $post['id'] : $post->ID;

  $categories = [];
  $taxonomy = $post_type.'_category';
  $terms = wp_get_post_terms( $post_ID, $taxonomy );

  if ( $terms ) :
    foreach ( $terms as $term ) :

      $link = get_term_link( $term->term_id, $taxonomy );

      $categories[] = [
        'wordpress_id'  => $term->term_id,
        'name'          => $term->name,
        'slug'          => $term->slug,
        'link'          => $link,
        'taxonomy'      => $term->taxonomy
      ];

    endforeach;
  endif;

  return $categories;
}
 
function create_api_categories_cpt() {

  $cpt_categories_rest_types = [
    'case',
    'inspo',
  ];

  foreach( $cpt_categories_rest_types as $type ) : 

    register_rest_field( $type,
      'cpt_categories',
      array(
           'get_callback'    => 'add_categories_to_cpt_rest',
           'update_callback' => null,
           'schema'          => null,
        )
    );

  endforeach;
}
 
add_action( 'rest_api_init', 'create_api_categories_cpt' );


/*==============================================================================
  # Add default excerpt to posts
==============================================================================*/

function add_post_excerpt( $new_status, $old_status, $post ) {

  if ( $new_status === 'publish' && !$post->post_excerpt && $post->post_content ) : 

    $new_excerpt = strlen($post->post_content) > 200 ? substr($post->post_content,0,200) : $post->post_content;
    $new_excerpt = strlen($post->post_content) >= 200 ? preg_replace('/([\s])[^\s]*$/i', ' ...', $new_excerpt) : $new_excerpt;

    wp_update_post([
      'ID'            => $post->ID,
      'post_excerpt'  => $new_excerpt
    ]);

  endif; 
}

//Called when a post is published or changed
add_action( 'transition_post_status', 'add_post_excerpt', 10, 3 );



/*==============================================================================
  # Add custom post-state for dummy-template
==============================================================================*/

add_filter('display_post_states', 'add_post_state_for_sub_pages');
function add_post_state_for_sub_pages( $states = array(), $post = 0 ) { 
  global $post;

  if ( get_page_template_slug( $post->ID ) === 'template-dummy.php' ) {
    $states[] = __('Dummysida - Visas ej besökare'); 
  } 

  return $states;
}


/*==============================================================================
  # Remove some flexible content layouts from acf on different templates/post types
==============================================================================*/

function add_layouts_admin_body_class( $classes ) {

  $template = get_page_template_slug();

  if ( $template ) {
    $template = str_replace('.php', '', $template);
    $classes .= $template;
  }

  return $classes;
}
add_filter('admin_body_class', 'add_layouts_admin_body_class');


/*==============================================================================
  # Customize TinyMCE
==============================================================================*/

if( !function_exists('base_extended_editor_mce_buttons') ){
  function base_extended_editor_mce_buttons($buttons) {
    return [
      'formatselect',
      'bold',
      'italic',
      'bullist',
      'numlist',
      'hr',
      'blockquote',
      'alignleft',
      'aligncenter',
      'alignright',
      'link',
      'dfw',
      'wp_adv'
    ];
  }
  add_filter('mce_buttons', 'base_extended_editor_mce_buttons', 999);
}

if( !function_exists('base_extended_editor_mce_buttons_2') ){
  function base_extended_editor_mce_buttons_2($buttons) {
    return [
      'forecolor',
      'pastetext',
      'removeformat',
      'charmap',
      'outdent',
      'indent',
      'undo',
      'redo',
    ];    
  }
  add_filter('mce_buttons_2', 'base_extended_editor_mce_buttons_2', 999);
}


if( !function_exists('base_custom_mce_format') ){
  function base_custom_mce_format($settings ) {

    return $settings ;
  }

  add_filter( 'teeny_mce_before_init', 'base_custom_mce_format', 999);
  add_filter( 'tiny_mce_before_init', 'base_custom_mce_format', 999);
}

/*==============================================================================
  # Attachment filter
==============================================================================*/

//This might prevent "TypeError: Cannot read property 'localFile' of null" error
if( !function_exists('customize_added_attachment') ){
  function customize_added_attachment(){
    global $wpdb;
    $wpdb_data = array(
      'post_parent' => 0
    );
    $where = array(
      'post_type' => 'attachment'
    );
    $update = $wpdb->update( $wpdb->prefix.'posts', $wpdb_data, $where );
  }
  add_action('add_attachment', 'customize_added_attachment');
  add_action('save_post', 'customize_added_attachment');
}


/*==============================================================================
  # Add page-attribute support for posts
==============================================================================*/


add_action( 'admin_init', 'posts_order_wpse_91866' );

function posts_order_wpse_91866() 
{
    add_post_type_support( 'post', 'page-attributes' );
}


/*==============================================================================
  # Add deploy page
==============================================================================*/

/*
 * Add admin page
 */

function theme_options_panel(){
  add_submenu_page( 'tools.php', 'Netlify Deploy', 'Netlify Deploy', 'manage_options', 'netlify-deploy', 'add_deploy_page');
}
add_action('admin_menu', 'theme_options_panel');


function add_deploy_page(){
  get_template_part( 'netlify-deploy' );
}


/*
 * Add javascript
 */

add_action( 'admin_footer', 'netlify_deploy_js' );

function netlify_deploy_js() { ?>
  <script type="text/javascript" >

  (function($) {
    $(document).ready(function($) {

      var data = {
        'action': 'ajax_netlify_deploy'
      };

      $('#netlify-deploy').on('click',function() {

        $(this).attr('disabled','disabled');

        $('#responses').append(`<div class="notice notice-info">
            <p>Deploy-förfrågan påbörjad. Kom ihåg att det kan ta flera minuter innan en build är färdig...</p>
        </div>`);

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
          $('#responses').append(response);
        });
      });

    });
  })( jQuery );

  </script> <?php
}


/*
 * Add ajax function
 */

add_action( 'wp_ajax_ajax_netlify_deploy', 'ajax_netlify_deploy' );

function ajax_netlify_deploy() {
    
  $error_message = '';
  
  // Setup cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.netlify.com/build_hooks/5d3184fd79e0d41fb303c3d5');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");
    curl_setopt($ch, CURLOPT_POST, 1);

    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_message = url_error($ch);
    }
    curl_close($ch);

    if ( !$error_message ) : ?>

      <div class="notice notice-info">
          <p>Förfrågan skickades till Netlify. Se status hos Netlify.</p>
      </div>

    <?php else : ?>

      <div class="notice notice-error">
          <p>Någonting gick fel vid förfrågan. Detta kan bero på ett fel i Wordpress eller ett fel vid mottagandet hos Netlify. Ta kontakt med utvecklare om problemet.</p>
      </div>

      <p>Ytterligare information:</p>
      <pre style="padding: 10px; border: 1px solid #000; background: #ccc;">
        <?php var_dump( $error_message ); ?>
      </pre>

    <?php endif;


  wp_die();
}