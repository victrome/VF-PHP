<?php

if (!defined('PATH_VF')) { // DO NOT REMOVE
  exit('VF IS NOT LOADED');
}

class Wordpress extends VF\Controller
{
  private $logs = [];
  private $user = null;
  public function __construct(VF\VFphp $vf)
  {
    $this->vf = $vf;
    if (!function_exists('wp_get_current_user')) {
      die("wp_get_current_user is not defined, is this a wordpress website?!");
    }
    $this->user = wp_get_current_user();
    if (!in_array('administrator', (array) $this->user->roles)) {
      die("Not authorized");
    }
  }
  private function getListApps()
  {
    $apps = scandir(PATH_APP);
    $apps = preg_grep(
      "/^(\.|\.\.|index\.php|\.htaccess)$|\.php\.js$/",
      $apps,
      PREG_GREP_INVERT
    );
    return $apps;
  }
  public function index()
  {
    $this->createPages();
    $viewData['logs'] = array_unique($this->logs);
    $this->view("routesInfo", null, $viewData);
  }
  public function createPages()
  {
    $routes = $this->getRoutes();
    $pageId = 0;
    foreach ($routes as $urlencoded => $routeData) {
      $route = urldecode($urlencoded);
      $id = $this->getWpPageIdBySlug($route);
      if ($id == 0) {
        $parentPageId = 0;
        $slug = $route;
        if (strpos($route, '/') !== false) {
          $routeArray = explode("/", $route);
          $slug = $routeArray[count($routeArray) - 1];
          unset($routeArray[count($routeArray) - 1]);
          $parentPageId = $this->getParentPage($routeArray, $routeData);
          if ($parentPageId == 0) {
            $this->log("error", "Error creating parent VF Page", "Route: '{$route}' - App: '{$routeData['app']}' - Action: {$routeData['action']}");
            continue;
          }
        }
        $pageId = $this->createPage($slug, $route, $routeData, $parentPageId);
        continue;
      }
      $app = get_post_meta($id, "VF_folder");
      $action = get_post_meta($id, "VF_action");
      if ($app && $action) {
        $this->updatePostMeta($pageId, "VF_folder", $routeData['folder']);
        $this->updatePostMeta($pageId, "VF_action", $routeData['action']);
      }
    }
  }
  public function createPage($slug, $route, $routeData, $parentId = 0)
  {
    $pageParams = array(
      'post_title' => "{$routeData['app']} - {$routeData['action']} ({$route})",
      'post_content' => '',
      'post_status' => 'publish',
      'post_type' => 'vfpages',
      'post_author' => $this->user->ID,
      'post_date' => date('Y-m-d H:i:s'),
      'post_name' => $slug
    );
    if ($parentId > 0) {
      $pageParams['post_parent'] = $parentId;
    }
    $pageId = wp_insert_post($pageParams);
    if ($pageId) {
      $this->updatePostMeta($pageId, "VF_folder", $routeData['folder']);
      $this->updatePostMeta($pageId, "VF_action", $routeData['action']);
      $this->log("success", "VF Page created", "Route: '{$route}' - App: '{$routeData['app']}' - Action: {$routeData['action']} - VF Page ID: {$pageId}");
      return $pageId;
    }
    $this->log("error", "Error creating VF Page", "Route: '{$route}' - App: '{$routeData['app']}' - Action: {$routeData['action']}");
    return 0;
  }
  public function getParentPage($routeArray, $routeData)
  {
    $parentPageId = 0;
    $routeCheck = "";
    foreach ($routeArray as $routePart) {
      $routeCheck .= $routePart;
      $parentPageId = $this->getWpPageIdBySlug($routeCheck);
      if ($parentPageId == 0) {
        $parentPageId = $this->createPage($routePart, $routeCheck, $routeData, $parentPageId);
      }
      $routeCheck .= "/";
    }
    return $parentPageId;
  }
  public function getRoutes()
  {
    $routes = $this->vf->getRoutes();
    ksort($routes);
    if ($this->vf->isRouteOnly) return $routes;
    $routes = array_merge($routes, $this->getAppsActions());
    ksort($routes);
    return $routes;
  }
  public function getAppsActions()
  {
    $apps = $this->getListApps();
    $actions = [];
    foreach ($apps as $folder) {
      $action = $this->getAppActions($folder);
      $actions = array_merge($actions, $action);
    }
    return ($actions);
  }
  public function getAppActions($folder)
  {
    $app = ucfirst(strtolower($folder));
    if (!file_exists(PATH_APP . $folder . "/app.php")) {
      return [];
    }
    include_once(PATH_APP . $folder . "/app.php");
    $class = new ReflectionClass($app);
    $actions = [];
    $actionsObj = $class->getMethods(ReflectionMethod::IS_PUBLIC);
    for ($i = 0; $i < count($actionsObj); $i++) {
      $class = $actionsObj[0]->class;
      $action = $actionsObj[$i]->name;
      $route = urlencode($folder . "/" . $action);
      if (substr($action, 0, 2) == "__") continue;
      if ($action == "noRoute") {
        $actions = [];
        break;
      }
      $actionArray = ['folder' => $folder, 'app' => $class, 'action' => $action];
      $actions[$route] = $actionArray;
    }
    return $actions;
  }
  public function getPluginRoute()
  {
    $route = "";
    if (defined('PLUGIN_ROUTE')) {
      $route = str_replace("/", "", PLUGIN_ROUTE);
      $route .= strlen($route) > 0 ? "/" : "";
    }
    return $route;
  }
  public function getWpPageIdBySlug($slug)
  {
    $id = 0;
    $args = array(
      'post_type'    =>  'vfpages',
      'pagename'        => $slug
    );
    $posts = new WP_Query($args);
    if (!$posts->have_posts()) {
      $slug = $this->getPluginRoute() . $slug;
      $args = array(
        'post_type'    =>  'page',
        'pagename'        => $slug
      );
      $posts = new WP_Query($args);
    }
    foreach ($posts->posts as $post) {
      $id = $post->ID;
      if ($post->post_type != 'vfpages') {
        $this->log("error", "Route already in use", "'{$slug}' is already in use on a non VF Page. Page ID {$post->ID}");
        $id = 0;
      }
    }
    return $id;
  }
  public function getWpPageByApp($app, $action = 'Main App')
  {
    $args = array(
      'meta_query'  =>  array(
        array(
          'key' => 'VF_folder',
          'value'  =>  $app
        ),
        array(
          'key' => 'VF_action',
          'value'  =>  $action
        )
      )
    );
    $posts = new WP_Query($args);
    return $posts->posts;
  }
  private function updatePostMeta($postId, $fieldName, $value = '')
  {
    if (!metadata_exists('post', $postId, $fieldName)) {
      add_post_meta($postId, $fieldName, $value);
    } else {
      update_post_meta($postId, $fieldName, $value);
    }
  }
  public function getLogs()
  {
    return $this->logs;
  }
  public function log($type, $title, $message)
  {
    $this->logs[] = ['type' => $type, 'title' => $title, 'message' => $message];
    return $this;
  }
  public function noRoute()
  {
    return;
  }
}
