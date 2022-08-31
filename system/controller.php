<?php

namespace VF;

if (!defined('PATH_VF')) { // DO NOT REMOVE
    exit('VF IS NOT LOADED');
}

class Controller
{
    /**
     * VFphp Instance
     *
     * @var VFphp
     */
    public VFphp $vf;

    public string $folder = '';

    public array $params = [];
    
    public function __construct(VFphp $vf)
    {
        $this->vf = $vf;
        $this->folder = $vf->folder;
        $this->params = $vf->params;
    }

    /**
     * Loads a model class
     * 
     * Gives access to model's functions
     * 
     * Example:
     * Loads the model 
     *      $model = $this->model("*fileNameWithoutExtension*");</i>
     * @param string $modelOrFolder model file name without extension or folder name
     * @param string $model *(optional)* array with data that you want to send to model
     * @return bool|object if file does not found it returns false else returns model object class
     */
    protected function model(string $modelOrFolder, string $model = null)
    {
        require_once 'model.php';
        $modelClass = ucfirst($modelOrFolder);
        $modelPath = PATH_APP . $this->folder . '/model/' . $modelOrFolder . '.php';
        if ($model != null && strlen($model) > 0) {
            $modelClass = ucfirst($model);
            $modelPath = PATH_APP . $modelOrFolder . '/model/' . $model . '.php';
        }
        if (!file_exists($modelPath)) {
            return false;
        }
        require_once $modelPath;
        $modelApp = new $modelClass($this->vf);
        $modelApp->vf = $this->vf;
        return $modelApp;
    }
    /**
     * Load a view file<BR>
     * This method can return a include view or html of itself
     * Example: <i>$this->view("basic", array('ID', 1), false);</i>
     * Example: <i>$bot_html = $this->view("basic", array('ID', 1), true);</i>
     * @param string $VF_name_view name of views file
     * @param array $VF_data array with data that you want to send to view
     * @param bool $VF_mode set if you want to require (false) or html (true) of view`s called
     * @return mixed if file does not found it returns false else if param 3 is false it requires the view else if param 3 is true it returns the html of this view
     */
    protected function view(string $viewOrFolder, string $view = null, array $data = array(), bool $htmlReturn = false)
    {
        if (is_array($data) and count($data) > 0) {
            extract($data, EXTR_PREFIX_SAME, "app");
        }
        $viewPath = PATH_APP . $this->folder . '/view/' . $viewOrFolder . '.php';
        if ($view != null && strlen($view) > 0) {
            $viewPath = PATH_APP . $viewOrFolder . '/view/' . $view . '.php';
        }
        if (!file_exists($viewPath)) {
            return false;
        }
        if ($htmlReturn) {
            ob_start();
            require_once $viewPath;
            $content = ob_get_clean();
            return $content;
        }
        require_once $viewPath;
    }

    /**
     * Filter INPUT or GET<BR>
     * This method filters INPUT or GET params
     * Example: <i>$bot_value = $this->input("NAME", "POST"); -- Filter as POST</i>
     * Example: <i>$bot_value = $this->input("NAME", "GET"); -- Filter as GET</i>
     * Example: <i>$bot_value = $this->input("NAME", "GET_POST"); -- Try to filter as POST if nothing is found try to filter as GET </i>
     * Example: <i>$bot_value = $this->input("NAME", "POST_GET"); -- Try to filter as GET if nothing is found try to filter as POST </i>
     * @param String $VF_name name Param GET or POST
     * @param String $VF_type type of filter (POST, GET, GET_POST, POST_GET)
     * @param String $VF_filter type of filter (check PHP documentation of 'filter_input')
     * @return mixed if nothing is found it returns false else it return a value it can be (String, Boolean, Integer...)
     */
    protected function input(string $name, string $type = "POST", bool $isArray = false, string $filter = "default")
    {
        $typesArray = is_array($type) ? $type : [$type];
        $filter = "FILTER_" . mb_strtoupper($filter, "UTF-8");
        foreach ($typesArray as $typeArray) {
            $type = mb_strtoupper($typeArray, "UTF-8");
            if ($isArray) {
                $value = filter_input(constant("INPUT_" . $type), $name, constant($filter), FILTER_REQUIRE_ARRAY);
                return $value;
            } else {
                $value = filter_input(constant("INPUT_" . $type), $name, constant($filter));
                return $value;
            }
        }
        return null;
    }
    /**
     * Set Session<BR>
     * Set a session in security mode
     * @param String $VF_name Session name
     * @param Object $VF_value session data (integer, array, string...)
     */
    protected function isSessionSet(string $name):bool
    { 
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (isset($_SESSION[base64_encode(date('m') . $name)])) {
            return true;
        }
        return false;
    }
    /**
     * Set Session<BR>
     * Set a session in security mode
     * @param String $VF_name Session name
     * @param Object $VF_value session data (integer, array, string...)
     */
    protected function setSession(string $name, $value):Controller
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION[base64_encode(date('m') . $name)] = $value;
        return $this;
    }
    /**
     * Get Session<BR>
     * Get a session in security mode
     * @param String $VF_name Session name
     * @return Object If session is not set returns false else return Session's value
     */
    protected function getSession(string $name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (isset($_SESSION[base64_encode(date('m') . $name)])) {
            return $_SESSION[base64_encode(date('m') . $name)];
        }
        return null;
    }
    /**
     * Delete Session<BR>
     * Delete a session in security mode
     * @param String $VF_name Session name
     */
    protected function unsetSession(string $name):bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (isset($_SESSION[base64_encode(date('m') . $name)])) {
            unset($_SESSION[base64_encode(date('m') . $name)]);
            return true;
        }
        return false;
    }

    /**
     * Gets a Helper<BR>
     *
     * @param String $name Helper folder
     */
    protected function helper(string $name)
    {
        if (!file_exists(PATH_HELPER . $name . '/functions.php')) {
            return false;
        }
        require_once PATH_HELPER . $name . '/functions.php';
        $className = ucfirst($name);
        if (!class_exists($className)) {
            return false;
        }
        $helper = new $className;
        return $helper;
    }

    public function assets():void
    {
        $file = implode("/", $this->params);
        if (strlen($file) > 0 && strpos($file, '.') !== false) {
            $filePath = PATH_VF . "/assets/" . $file;
            if ($this->params[0] == 'app') {
                $app = $this->params[1];
                unset($this->params[0]);
                unset($this->params[1]);
                $file = implode("/", $this->params);
                $filePath = PATH_APP . $app . "/assets/" . $file;
            }
            if ($this->params[0] == 'helper') {
                $helper = $this->params[1];
                unset($this->params[0]);
                unset($this->params[1]);
                $file = implode("/", $this->params);
                $filePath = PATH_HELPER . $helper . "/assets/" . $file;
            }
            $fileInfo = pathinfo($filePath);
            $fileMime = $this->getFileMimeType($fileInfo['extension']);
            if ($fileMime && file_exists($filePath)) {
                header("Content-type: " . $fileMime);
                echo file_get_contents($filePath);
            }
        }
    }
    private function getFileMimeType(string $ext):string
    {
        $mimes = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        if (isset($mimes[$ext])) {
            return $mimes[$ext];
        }
        return '';
    }
}
