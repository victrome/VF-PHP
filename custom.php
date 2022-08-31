<?php

namespace VF;

class Custom
{
  private VFphp $vf; 
  /************************************
  /************ VF METHODS ************
  /************************************/
  public function onCustomLoad(): void
  {
    //define('PLUGIN_ROUTE',    '/');
  }
  public function onVfLoad(VFphp $vf): void
  {
    $this->vf = $vf;
    //$vf->isRouteOnly = true;
    //$vf->route('helloworld', 'index', 'hello');
  }
  public function onAppLoad(VFphp $vf, $app = null): void
  {
  }

  /************************************
  /******** WORDPRESS METHODS **********
  /************************************/

  public function onPluginActivation(): void
  {
    //if (get_role('VF') === null) add_role('VF', 'VF User');
  }
  public function onPluginDeactivation(VFphp $vf): void
  {
  }
  public function onPluginLoad(VFphp $vf): void
  {
    //Wordpress Only
    //Sets nonce and makes it available via javascript
    //add_action('wp_enqueue_scripts', function () {
    //  $data['nonce'] = wp_create_nonce('btyse_nonce');
    //  $data['urlAjax'] = admin_url('admin-ajax.php');
    //  wp_localize_script('jquery', 'jsNonce', $data);
    //});

    //Wordpress Only
    //Sets nonce and makes it available on the first VF PHP instance
    //add_action('plugins_loaded', function () use ($vf) {
    //  $vf->setData('nonce', wp_create_nonce('btyse_nonce'));
    //});

    //Wordpress Only
    //Sets ajax function for non logged users
    //add_action('wp_ajax_nopriv_vf_ajax', function () use ($vf) {
    //  $vf->app('helloworld', 'ajax');
    //  die();
    //});

    //Wordpress Only
    //Sets ajax function for logged users
    //add_action('wp_ajax_get_vf_ajax', function () use ($vf) {
    //  $vf->app('helloworld', 'ajax');
    //  die();
    //});
  }


  /************************************
  /********** CUSTOM METHODS **********
  /************************************/

  //Declare custom methods here
}
