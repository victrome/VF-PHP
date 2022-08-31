<?php
/*
Plugin Name: VF-PHP
Description: <a href="http://vfphp.com">VF-PHP</a> is a PHP MVC Framework 
Author: <a href="http://victro.me">Victor Mendes</a> | <a href="http://vfphp.com/documentation">Documentation</a>
Version: 0.0.9
*/
define('VF_INDEX',__FILE__);
if(!function_exists("plugin_dir_path")){
  function plugin_dir_path($file){
    return dirname($file)."/";
  }
}
require_once(plugin_dir_path(__FILE__)."custom.php");
$customClass = new VF\Custom();
if(method_exists($customClass, 'onCustomLoad')) { $customClass->onCustomLoad(); } 

include(plugin_dir_path(__FILE__).'vf.php'); 
if(method_exists($customClass, 'onVfLoad')) { $customClass->onVfLoad($vf); } 


if($vf->isWP && file_exists(PATH_APP."wordpress/include.php")){
  require_once(PATH_APP."wordpress/include.php");
}

if(!$vf->isWP){
  $vf->app();
}