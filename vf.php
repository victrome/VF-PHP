<?php

namespace VF;

/**
 * VFphp class
 * 
 * @package VFphp
 * @author Victor Mendes
 */
class VFphp
{
  /**
   * Current loaded app folder
   *
   * @var string
   */
  public string $folder = '';

  /**
   * Current loaded app name
   *
   * @var string
   */
  public string $app;

  /**
   * Current loaded app action
   *
   * @var string
   */
  public string $action = '';

  /**
   * Custom Class Instance
   *
   * @var Custom
   */
  public Custom $custom;
  /**
   * Data storage
   *
   * @var array
   */
  public array $data = [];

  /**
   * Get var name to be used as VF params
   *
   * @var string
   */
  public string $getParam = "vf";

  /**
   * VF logs
   *
   * @var array
   */
  private array $logs = [];
  /**
   * Defines if VF is running on a wordpress installation
   *
   * @var boolean
   */
  public bool $isWP = false;

  /**
   * Defines if VF will ignore class and method names when creating routes
   *
   * @var boolean
   */
  public bool $isRouteOnly = false;

  /**
   * Params are used to pass parameters to the app method called
   *
   * @var array
   */
  public array $params = [];

  /**
   * Where routes are stored
   *
   * @var array
   */
  private array $routes = [];
  /**
   * Site url
   *
   * if null VF PHP will try to get the site url using _SERVER global var
   * @var null|string
   */
  public ?string $siteUrl = null;
  /**
   * Stores all new instances made from this VF instance
   * 
   * Stores VF and App instances
   *
   * @var array
   */
  private array $instances = [];

  /**
   * Construct method
   * 
   * Defines path constants on first instance
   */
  public function __construct(Custom $custom)
  {
    $this->custom = $custom;
    if (defined('PATH_VF')) {
      if (defined('WP_PLUGIN_DIR')) {
        $this->isWP = true;
      }
      return;
    }
    if (defined('WP_PLUGIN_DIR')) {
      $this->isWP = true;
      define("PATH_VF",   plugin_dir_path(__FILE__));
    } else {
      define("PATH_VF",   "");
    }
    define('PATH_CONFIG', PATH_VF . "system/");
    define('PATH_APP',    PATH_VF . "app/");
    define('PATH_ASSETS', PATH_VF . "assets/");
    define('PATH_HELPER', PATH_VF . "helper/");
  }
  /**
   * Gets params from URL and set them to folder, action and params vars
   *
   * @return void
   */
  public function getVFParams(): void
  {
    if (!$this->isWP) {
      $folderVf = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
      $uri = strtok($_SERVER["REQUEST_URI"], '?');
      $uri = $this->strReplaceOnce($folderVf, "", $uri);
      $uri = str_replace("//", "/", $uri);
      $uri = preg_replace('#^/#', '', $uri);
      $uriArray = explode("/", $uri);

      $routeApp = $this->getRoute($uriArray);
      if (isset($routeApp['action'])) {
        $this->action = $routeApp['action'];
        $this->folder = $routeApp['folder'];
        $this->params = $routeApp['params'];
        return;
      }
      if ($this->isRouteOnly || !isset($uriArray[0])) {
        return;
      }

      $this->folder = $uriArray[0];
      $this->action = isset($uriArray[1]) ? $uriArray[1] : "index";
      unset($uriArray[0]);
      unset($uriArray[1]);
      $uriArray = array_values($uriArray);
      $uriArray = array_filter($uriArray,'strlen');
      $this->params = $uriArray;
    } else {
      $wpParamsString = isset($_GET[$this->getParam]) ? (string)$_GET[$this->getParam] : '';
      $wpParams = explode("/", $wpParamsString);
      $wpParams = array_filter($wpParams,'strlen');
      $this->params = $wpParams;
    }
  }
  public function log(string $type, string $message){
    $this->logs[$type][] = $message;
  }
  public function getLogs(string $type = null){
    if($type === null) return $this->logs;
    return $this->logs[$type];
  }
  /**
   * Starts app
   *
   * @param string|null $folder app folder (null default)
   * @param string|null $action app action (null default)
   * @param array|null $params app params, it will be used as parameters to the method called (null default)
   * @param boolean $loadCustom if true will call the method onAppLoad from the Custom class (true default)
   * @return mixed it will depend on what the app called returns
   */
  public function app(string $folder = null, string $action = null, array $params = null, bool $storeInstance = true, bool $loadCustom = true)
  {
    $this->getVFParams();
    $this->app = $folder == null ? ucfirst($this->folder) : ucfirst($folder);
    $this->folder = $folder == null ? $this->folder : $folder;
    $this->action = $action == null ? $this->action : $action;
    $this->params = !is_array($params) ? $this->params : $params;

    require_once(PATH_CONFIG . "controller.php");
    if (!file_exists(PATH_APP . $this->folder . '/app.php')) {
      $this->log("error", "VF-PHP: Missing file app/{$this->folder}/app.php");
      return false;
    }
    require_once(PATH_APP . $this->folder . '/app.php');
    if (!class_exists($this->app)) {
      $this->log("error", "VF-PHP: Missing class {$this->app} on app/{$this->folder}/app.php");
      return false;
    }
    $app = $storeInstance && isset($this->instances['app'][$folder]) ? $this->instances['app'][$folder] : new $this->app($this);
    if ($storeInstance && !isset($this->instances['app'][$folder])) {
      $this->instances['app'][$folder] = $app;
    }
    if (!method_exists($app, $this->action)) {
    }
    if ($action === null) {
      if (!$loadCustom) return $app;
      if ($this->custom && method_exists($this->custom, 'onAppLoad')) {
        $this->custom->onAppLoad($this, $app);
      }
      $this->clearParams();
      return $app;
    }
    $returnApp = call_user_func_array(array($app, $this->action), $this->params);
    if (!$loadCustom) return $returnApp;
    if ($this->custom && method_exists($this->custom, 'onAppLoad')) {
      $this->custom->onAppLoad($this, $app);
    }
    $this->clearParams();
    return $returnApp;
  }

  private function clearParams(){
    $this->app = '';
    $this->folder = '';
    $this->action = '';
    $this->params = [];
  }

  /**
   * Gets site url
   * 
   * If siteUrl is defined it will be returned instead
   *
   * @return string
   */
  public function getSiteUrl(): string
  {
    if (filter_var($this->siteUrl, FILTER_VALIDATE_URL)) {
      return $this->siteUrl;
    }
    if (isset($_SERVER['HTTP_HOST'])) {
      $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
      $hostname = $_SERVER['HTTP_HOST'];
      $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
      $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), 0, PREG_SPLIT_NO_EMPTY);
      $core = $core[0];

      $tmplt = "%s://%s%s";
      return sprintf($tmplt, $http, $hostname, $dir);
    } else return 'http://localhost/';
  }

  /**
   * Adds route to VF PHP
   *
   * @param string $folder app folder
   * @param string $action app action
   * @param string $route url route
   * @return VFphp
   */
  public function route(string $folder, string $action, string $route): VFphp
  {
    $route = urlencode($route);
    if (!in_array($route, $this->routes)) {
      $this->routes[$route] = ['action' => $action, 'folder' => $folder];
    }
    return $this;
  }

  /**
   * Returns VF PHP routes
   *
   * @return array array keys [action, folder]
   */
  public function getRoutes(): array
  {
    return $this->routes;
  }

  /**
   * Gets route details - folder ,action and params -
   * 
   * @param array $url url in array - after the folder and 
   * action are found the rest of the array values will be used as params
   * @return array array keys [action, folder, params]
   */
  public function getRoute(array $url): array
  {
    $vfParams = [];
    while (count($url) > 0) {
      $route = implode("/", $url);
      $route = urlencode($route);
      if (isset($this->routes[$route])) {
        krsort($vfParams);
        $vfRoute = $this->routes[$route];
        $vfRoute['params'] = $vfParams;
        return $vfRoute;
      }
      $vfParams[] = array_pop($url);
    }
    return [];
  }

  /**
   * Returns page url based on folder and action
   *
   * @param string $folder app folder
   * @param string $action app action
   * @param boolean $arrayReturn if true returns array else returns string (false is the default)
   * @return mixed
   */
  public function getPageUrl(string $folder, string $action, bool $arrayReturn = false)
  {
    $urlReturn = [];
    if ($this->isWP) {
      $args = array(
        'post_type' => 'vfpages',
        'meta_query' => array(
          array(
            'key' => 'VF_folder',
            'value' => $folder,
          ),
          array(
            'key' => 'VF_action',
            'value' => $action,
          ),
        ),
      );
      $query = new \WP_Query($args);
      if ($query->have_posts()) {
        $pages = $query->posts;
        foreach ($pages as $page) {
          $urlReturn[] = esc_url(get_permalink($page->ID)); //wordpress function
        }
      }
    } else {
      $routes = $this->getRoutes();
      foreach ($routes as $route => $routeInfo) {
        if ($routeInfo['folder'] == $folder && $routeInfo['action'] == $action) {
          $urlReturn[] = $this->getSiteUrl() . urldecode($route);
        }
      }
      if (!$this->isRouteOnly) {
        $urlReturn[] = $this->getSiteUrl() . $folder . "/" . $action;
      }
    }
    if ($arrayReturn) {
      return $urlReturn;
    } else if (count($urlReturn) > 0) {
      return $urlReturn[0];
    }
    return false;
  }

  /**
   * Replaces the first occurrence of a string
   *
   * @param string $strPattern search
   * @param string $strReplacement replacement
   * @param string $string the string
   * @return string 
   */
  public function strReplaceOnce(string $strPattern, string $strReplacement, string $string): string
  {

    if (strpos($string, $strPattern) !== false) {
      $occurrence = strpos($string, $strPattern);
      return substr_replace($string, $strReplacement, $occurrence, strlen($strPattern));
    }
    return $string;
  }

  /**
   * Sets data
   * 
   * The data can be accessed by any app running on this instance
   * or by any other VF instance that cloned the data from this instance (default)
   *
   * @param string $name Data name, it will be used to get the data later
   * @param mixed $data The data 
   * @param boolean $merge If true and the data indice was already set and the data is array, it will merge the previous data with param data
   * @return VFphp
   */
  public function setData(string $name, $data, $merge = true): VFphp
  {
    $this->data[$name] = isset($this->data[$name]) && is_array($this->data[$name]) && $merge ? array_merge($this->data[$name], $data) : $data;
    return $this;
  }

  public function getData(string $name)
  {
    return isset($this->data[$name]) ? $this->data[$name] : null;
  }

  public function unsetData(string $name): bool
  {
    if (isset($this->data[$name])) {
      unset($this->data[$name]);
      return true;
    }
    return false;
  }

  public function clearData()
  {
    $this->data = [];
    return $this;
  }
  /**
   * Creates a new instance of VFphp class
   *
   * @param array|null $data VFphp data
   * @param boolean $loadCustom defines if the method onVfLoad from class Custom will be called
   * @return VFphp VFphp instance
   */
  public function new(bool $clone = true, bool $loadCustom = true): VFphp
  {
    $vf = new VFphp($this->custom);
    $vf->data = $clone ? $this->data : [];
    $vf->isWP = $this->isWP;
    if (!$loadCustom) return $vf;
    if ($this->custom && method_exists($this->custom, 'onVfLoad')) {
      $this->custom->onVfLoad($this);
    }
    return $vf;
  }
}

$vf = new VFphp($customClass);
