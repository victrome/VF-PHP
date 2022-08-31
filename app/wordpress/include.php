<?php

function vfAdminMenu()
{
      add_menu_page(
        'VF PHP', // Title of the page
        'VF PHP', // Text to show on the menu link
        'update_plugins', // Capability requirement to see the link
        'edit.php?post_type=vfpages',
    );
   add_submenu_page(
        'edit.php?post_type=vfpages',
        'Pages',
        'Pages',
        'update_plugins',
        'edit.php?post_type=vfpages'
    );
    add_submenu_page(
      'edit.php?post_type=vfpages',
      'Check Routes',
      'Check Routes',
      'update_plugins',
      'vf_settings',
      'wpCheckRoute'
    );
}

add_action( 'admin_menu', 'vfAdminMenu' );

function wpCheckRoute(){
  GLOBAL $vf;
  $vf->app("wordpress", "index");
}

/*
* Creating a function to create our CPT
*/
 
function customPageType() {  
      $args = array(
          'label'               => __( 'VF Pages', 'twentythirteen' ),
          'description'         => __( 'VF Pages', 'twentythirteen' ),
          'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes', 'templates'),
          'rewrite'             => array( 'slug' => 'vfphp', 'with_front' => false ),
          'hierarchical'        => true,
          'public'              => true,
          'show_ui'             => true,
          'show_in_menu'        => false,
          'show_in_nav_menus'   => false,
          'show_in_admin_bar'   => false,
          'can_export'          => true,
          'has_archive'         => true,
          'exclude_from_search' => false,
          'publicly_queryable'  => true,
          'capability_type'     => 'page',
          'map_meta_cap' => true,
      );
      if (defined( 'PLUGIN_ROUTE' ) ) {
        $args['rewrite'] = array( 'slug' => PLUGIN_ROUTE, 'with_front' => false );
      }
      register_post_type( 'VF Pages', $args );
   
  }
   
add_filter('single_template', 'vf_template_single');

function vf_template_single($single) {
  global $post;
  if ($post->post_type == 'vfpages') {
      if (file_exists(PATH_APP . 'wordpress/template.php')) {
          return PATH_APP . 'wordpress/template.php';
      }
      exit("VF template could not be found");
  }
  return $single;
}

add_filter('archive_template', 'vf_template_archive');

function vf_template_archive($archive) {
    global $post, $vf;
    $app = get_post_meta($post->ID, "VF_folder", true);
    $action = get_post_meta($post->ID, "VF_action", true);
    if($app && $action){
      $vf->app($app, $action);
    }
    return $archive;
}
if(file_exists(PATH_VF."custom.php") && method_exists($customClass, 'onPluginLoad')) { 
    $customClass->onPluginLoad($vf); 
    
} 


add_action( 'init', 'customPageType', 0 );

register_activation_hook(VF_INDEX, 'vfPluginActivation');
function vfPluginActivation(){
  add_option("VF-PHP", 1);
  if(file_exists(PATH_VF."custom.php")){
    $customClass = new VF\Custom();
    if(method_exists($customClass, 'onPluginActivation')) { 
      $customClass->onPluginActivation(); 
    }
  }
}

register_deactivation_hook(VF_INDEX, 'vfPluginDeactivation');
function vfPluginDeactivation(){
  GLOBAL $vf, $customClass;
  delete_option("VF-PHP");
  if(file_exists(PATH_VF."custom.php") && method_exists($customClass, 'onPluginDeactivation')) { 
    $customClass->onPluginDeactivation($vf); 
  }
}
?>