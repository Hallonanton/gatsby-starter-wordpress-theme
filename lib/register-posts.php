<?php
/*==============================================================================

  # Register custom post_types
  # Register custom taxonomies

==============================================================================*/

/*==============================================================================
  # Register custom post_types
==============================================================================*/

/**
 * Register Artiklar
 */

$labels = array(
  'name'                => 'Artiklar', 
  'menu_name'           => 'Artiklar',
  'singular_name'       => 'Artikel',
  'search_items'        => 'Sök artikel',
  'all_items'           => 'Alla artiklar',
  'edit_item'           => 'Ändra artikel',
  'update_item'         => 'Uppdatera artikel',
  'add_new_item'        => 'Skapa ny artikel',
  'new_item'            => 'Skapa ny artikel',
  'view_item'           => 'Visa artikel',
);
$args = array(
  'labels'              => $labels,
  'public'              => true,
  'exclude_from_search' => true,
  'publicly_queryable'  => true,
  'show_ui'             => true,
  'show_in_nav_menus'   => true,
  'show_in_menu'        => true,
  'show_in_admin_bar'   => true,
  'has_archive'         => true,
  'show_in_rest'        => true,
  'menu_icon'           => 'dashicons-admin-customizer',
  'rewrite'             => array( 'slug' => 'artiklar', 'with_front' => false ),
  'supports'            => array('title','page-attributes')
  );
register_post_type( 'article', $args );



/*==============================================================================
  # Register custom taxonomies
==============================================================================*/

/**
 * article_category
 */

$labels = array(
  'name'                => 'Kategorier',
  'menu_name'           => 'Kategorier',
  'singular_name'       => 'Kategori',
  'search_items'        => 'Sök kategori',
  'all_items'           => 'Alla kategorier',
  'edit_item'           => 'Ändra kategori',
  'update_item'         => 'Uppdatera kategori',
  'add_new_item'        => 'Skapa ny kategori',
  'new_item'            => 'Skapa ny kategori',
  'view_item'           => 'Visa kategori',
);
$args = array(
  'public'              => false,
  'publicly_queryable'  => false,
  'show_ui'             => true,
  'show_in_menu'        => true,
  'show_in_nav_menus'   => true,
  'show_admin_column'   => true,
  'hierarchical'        => true,
  'query_var'           => true,
  'show_in_rest'        => true,
  'labels'              => $labels,
  //'rewrite'             => array( 'slug' => 'inspo-kategori' ), Rewrite done in theme_functions
);
register_taxonomy( 'article_category', array( 'article' ), $args );