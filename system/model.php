<?php

namespace VF;

if (!defined('PATH_VF')) { // DO NOT REMOVE
    exit('VF IS NOT LOADED');
}
require_once PATH_CONFIG . "database.php";
require_once PATH_CONFIG . "result.php";

/**
 * APP MODEL Class
 * 
 * Class required class to access model's functions
 */
class Model extends Database
{
    public  VFphp   $vf;
    private array   $table = array();
    private array   $select = array();
    private array   $orderBy = array();
    private array   $groupBy = array();
    private string  $limit = "";
    private array   $having = array();
    private array   $where = array();
    private array   $joins = array();
    private string  $dbConnectionString = "defaultConnection";
    private ?\PDO   $connection = null;


    public function __construct(VFphp $vf)
    {
        $this->vf = $vf;
    }

    public function __destruct()
    {
        $this->connection = null;
    }
    /**
     * <b>Connection</b> Should never be used
     * @return defaultConnection();
     */
    protected function conn(): \PDO
    {
        if ($this->connection == null) {
            $this->connection = call_user_func(array("VF\Model", $this->dbConnectionString));
        }
        return $this->connection;
    }

    public function setConnection(string $connection): void
    {
        $this->dbConnectionString = $connection;
    }
    /**
     * Select fields from database<BR>
     * Exemple: <i>$this->select("ID");</i>
     * @param String $VF_select_q field name
     */
    protected function select(string $select): Model
    {
        $this->select[] = $select;
        return $this;
    }
    /**
     * set table to query <BR>
     * Exemple: <i>$this->from("Table");</i>
     * @param String $VF_table_q table/view name
     */
    protected function from(string $table): Model
    {
        $this->table[] = $table;
        return $this;
    }
    /**
     * set a table join (left join)
     * @param  String $VF_table_q table name
     * @param  String $VF_on      ON query
     */
    protected function leftJoin(string $table, string $on): Model
    {
        $this->joins[] = " left join " . $table . " on " . $on;
        return $this;
    }
    /**
     * set a table join (right join)
     * @param  String $VF_table_q table name
     * @param  String $VF_on      ON query
     */
    protected function rightJoin(string $table, string $on): Model
    {
        $this->joins[] = " right join " . $table . " on " . $on;
        return $this;
    }
    /**
     * set a table join (inner join)
     * @param  String $VF_table_q table name
     * @param  String $VF_on      ON query
     */
    protected function innerJoin(string $table, string $on): Model
    {
        $this->joins[] = " inner join " . $table . " on " . $on;
        return $this;
    }
    /**
     * set where clause to query<BR>
     * When you use more than once it takes 'and' to join where clauses<BR>
     * Exemple: <i>$this->where("ID > 1");</i>
     * @param String $VF_where_q where clause
     */
    protected function where(string $where, string $whereValue = ""): Model
    {
        $prefix = "";
        if (count($this->where) > 0) {
            $prefix = "and ";
        }
        if (is_string($whereValue) && $whereValue != "") {
            $whereValue = " '" . $whereValue . "'";
        }
        $this->where[] = $prefix . $where . $whereValue;
        return $this;
    }
    /**
     * set where clause to query<BR>
     * When you use more than once it takes 'or' to join where clauses<BR>
     * Exemple: <i>$this->where_or("ID > 1");</i>
     * @param String $VF_where_q where clause
     */
    protected function whereOr(string $where, string $whereValue = ""): Model
    {
        $prefix = "";
        if (count($this->where) > 0) {
            $prefix = "or ";
        }
        if (is_string($whereValue) && $whereValue != "") {
            $whereValue = " '" . $whereValue . "'";
        }
        $this->where[] = $prefix . $where . $whereValue;
        return $this;
    }
    /**
     * set order by clause to query<BR>
     * Exemple: <i>$this->orderby("ID asc");</i>
     * @param String $VF_order_q order clause
     */
    protected function orderBy(string $field, string $order = ""): Model
    {
        $this->orderBy[] = $field . " " . $order;
        return $this;
    }
    /**
     * set limit clause to query<BR>
     * Exemple: <i>$this->limit("10");</i>
     * @param String $VF_limit_q limit clause
     */
    protected function limit(string $offset, string $rowCount = ""): Model
    {
        $this->limit = " limit " . $offset . (strlen($rowCount) > 0 ? "," . $rowCount : "");
        return $this;
    }
    /**
     * set group by clause to query<BR>
     * Exemple: <i>$this->groupby("ID");</i>
     * @param String $VF_groupby_q group by clause
     */
    protected function groupBy(string $groupby): Model
    {
        $this->groupBy[] = $groupby;
        return $this;
    }
    /**
     * set having clause to query<BR>
     * Exemple: <i>$this->having("ID > 10");</i>
     * @before groupby()
     * @param String $VF_having_q having clause
     */
    protected function having(string $having, string $havingValue = ""): Model
    {
        if (count($this->groupBy) == 0) return false;
        $prefix = "";
        if (count($this->having) > 0) {
            $prefix = "and ";
        }
        if (is_string($havingValue) && $havingValue != "") {
            $havingValue = " '" . $havingValue . "'";
        }
        $this->having[] = $prefix . $having . $havingValue;
        return $this;
    }
    /**
     * set having clause to query<BR>
     * Exemple: <i>$this->having("ID > 10");</i>
     * @before groupby()
     * @param String $VF_having_q having clause
     */
    protected function havingOr(string $having, string $havingValue = ""): Model
    {
        if (count($this->groupBy) == 0) return false;
        $prefix = "";
        if (count($this->having) > 0) {
            $prefix = "or ";
        }
        if (is_string($havingValue) && $havingValue != "") {
            $havingValue = " '" . $havingValue . "'";
        }
        $this->having[] = $prefix . $having . $havingValue;
        return $this;
    }
    private function getSelect(): string
    {
        $select = implode(", ", $this->select);
        return $select;
    }
    private function getTable(): string
    {
        $table = implode(", ", $this->table);
        return $table;
    }
    private function getJoins(): string
    {
        if (count($this->joins) == 0) return "";
        $joins = implode(" ", $this->joins);
        return $joins;
    }
    private function getWhere(): string
    {
        if (count($this->where) == 0) return "";
        $where = " where ";
        $where .= implode(" ", $this->where);
        return $where;
    }
    private function getOrderBy(): string
    {
        if (count($this->orderBy) == 0) return "";
        $order = " order by ";
        $order .= implode(", ", $this->orderBy);
        return $order;
    }
    private function getGroupBy(): string
    {
        if (count($this->groupBy) == 0) return "";
        $group = " group by ";
        $group .= implode(", ", $this->groupBy);
        return $group;
    }
    private function getHaving(): string
    {
        if (count($this->having) == 0) return "";
        $having = " having ";
        $having .= implode(", ", $this->having);
        return $having;
    }
    /**
     * Sent a query to database<BR>
     * Exemple: <i>$this->db_query("select id, name from bot_names");</i>
     * @param String $VF_query SQL Query
     * @param Boolean $VF_successLog insert a log if the query has success (defined as false)
     */
    protected function dbQuery(string $query, array $params = array()): Result
    {
        $result = null;
        try {
            $prepareQuery = $this->conn()->prepare($query);
            foreach ($params as $paramKey => $param) {
                if (is_array($param)) {
                    $prepareQuery->bindValue($paramKey, $param['value'], $param['PDO']);
                } else {
                    $prepareQuery->bindValue($paramKey, $param);
                }
            }
            $executedQuery = $prepareQuery->execute();
            $result = new Result($this->vf, $prepareQuery, $this->conn()->lastInsertId(), $query);
        } catch (\PDOException $e) {
            $result = new Result($this->vf, null, 0, $query, $e->getMessage());
        }
        return ($result);
    }
    /**
     * Sent a select query to database<BR>
     * Exemple: <i>$this->db_select("id");</i>
     * @param Bolean $VF_cleanFields clean defined params (select, from, where, group by...) (defined as true)
     * @param Boolean $VF_successLog insert a log if the query has success (defined as false)
     */
    protected function dbSelect(bool $cleanFields = true): Result
    {
        $select = $this->getSelect();
        $from = $this->getTable();
        $joins = $this->getJoins();
        $where = $this->getWhere();
        $group = $this->getGroupBy();
        $having = $this->getHaving();
        $order = $this->getOrderBy();
        $limit = $this->limit;
        $result = null;
        $query = 'select ' . $select . ' from ' . $from . $joins . $where . $group . $having . $order . $limit;
        try {
            $prepareQuery = $this->conn()->prepare($query);
            $selectObj = $prepareQuery->execute();
            $result = new Result($this->vf, $prepareQuery, 0, $query);
        } catch (\PDOException $e) {
            $result = new Result($this->vf, null, 0, $query, $e->getMessage());
        }

        if ($cleanFields == true) {
            $this->table = array();
            $this->select = array();
            $this->where = array();
            $this->orderBy = array();
            $this->groupBy = array();
            $this->limit = "";
            $this->having = array();
            $this->joins = array();
        }
        return $result;
    }
    /**
     * Sent a insert query to database<BR>
     * Exemple:<BR>
     * <pre>
     *     <i>$bot_data = array('name' => 'Jean', 'robot' => victro);</i>
     *     <i>$this->db_insert("owner", $bot_data);</i>
     * </pre>
     * @param String $VF_table table name
     * @param Array $VF_data array with field name and value (check exemple)
     * @param Boolean $VF_successLog insert a log if the query has success (defined as false)
     */
    protected function dbInsert(string $table, array $data): Result
    {
        $result = null;
        $fieldsArray = array();
        $valuesArray = array();
        foreach ($data as $key => $value) {
            $fieldsArray[] = "{$key}";
            $valuesArray[] = ":{$key}";
        }
        $fields = implode(", ", $fieldsArray);
        $values = implode(", ", $valuesArray);
        $query = "insert into {$table} ({$fields}) values ({$values})";
        $queryDebug = $query;
        try {
            $conn = $this->conn();
            $prepareQuery = $conn->prepare($query);
            foreach ($data as $key => $value) {
                if (is_numeric($value)) {
                    $prepareQuery->bindValue(":" . $key, $value, \PDO::PARAM_INT);
                    $queryDebug = str_replace(':' . $key, $value, $queryDebug);
                } else {
                    $prepareQuery->bindValue(":" . $key, $value, \PDO::PARAM_STR);
                    $queryDebug = str_replace(':' . $key, "'{$value}'", $queryDebug);
                }
            }
            $execQuery = $prepareQuery->execute();
            $result = new Result($this->vf, $prepareQuery, $this->conn()->lastInsertId($table), $queryDebug);
        } catch (\PDOException $e) {
            $result = new Result($this->vf, null, 0, $queryDebug, $e->getMessage());
        }
        return $result;
    }
    /**
     * Sent a update query to database<BR>
     * To database security to send a update query is necessary to use $this->where("") method
     * Exemple:<BR>
     * <pre>
     *     <i>$this->where("ID = 1")</i>
     *     <i>$bot_data = array('name' => 'Jean', 'robot' => victro);</i>
     *     <i>$this->db_update("owner", $bot_data);</i>
     * </pre>
     * @param String $VF_table table name
     * @param Array $VF_data array with field name and value (check exemple)
     * @param Boolean $VF_successLog insert a log if the query has success (defined as false)
     */
    protected function dbUpdate(string $table, array $data, bool $cleanFields = true): Result
    {
        $where = $this->getWhere();
        $limit = $this->limit;
        $order = $this->getOrderBy();
        $result = new Result($this->vf, null, 0,null);
        if (strlen($where) == 0) {
            new Result($this->vf, null, 0, "", "SAFETY: no where clause found");
        }
        $fieldsArray = array();
        foreach ($data as $key => $value) {
            $fieldsArray[] = "{$key} = :{$key}";
        }
        $fields = implode(", ", $fieldsArray);
        $query = "update {$table} set {$fields} {$where} {$order} {$limit}";
        $queryDebug = $query;
        try {
            $prepareQuery = $this->conn()->prepare($query);
            foreach ($data as $key => $value) {
                if (is_numeric($value)) {
                    $prepareQuery->bindValue(":" . $key, $value, \PDO::PARAM_INT);
                    $queryDebug = str_replace(':' . $key, $value, $queryDebug);
                } else {
                    $prepareQuery->bindValue(":" . $key, $value, \PDO::PARAM_STR);
                    $queryDebug = str_replace(':' . $key, "'{$value}'", $queryDebug);
                }
            }
            $execQuery = $prepareQuery->execute();
            $result = new Result($this->vf, $prepareQuery, 0, $queryDebug);
        } catch (\PDOException $e) {
            $result = new Result($this->vf, null, 0, $query, $e->getMessage());
        }
        if ($cleanFields) {
            $this->where = array();
            $this->orderBy = array();
            $this->limit = "";
        }
        return ($result);
    }
    /**
     * Sent a delete query to database<BR>
     * To database security to send a delete query is necessary to use $this->where("") method
     * Exemple:<BR>
     * <pre>
     *     <i>$this->where("ID = 1")</i>
     *     <i>$this->db_delete("owner");</i>
     * </pre>
     * @param String $VF_table table name
     * @param Boolean $VF_successLog insert a log if the query has success (defined as false)
     */
    protected function dbDelete(string $table, bool $cleanFields = true): Result
    {
        $where = $this->getWhere();
        $result = new Result($this->vf, null, 0,null);
        if (strlen($where) == 0) {
            new Result($this->vf, null, 0, "", "SAFETY: no where clause found");
        }

        $query = "Delete from {$table} {$where}";
        $queryDebug = $query;
        try {
            $prepareQuery = $this->conn()->prepare($query);
            $execQuery = $prepareQuery->execute();
            $result = new Result($this->vf, $prepareQuery, 0, $queryDebug);
        } catch (\PDOException $e) {
            $result = new Result($this->vf, null, 0, $query, $e->getMessage());
        }
        if ($cleanFields) {
            $this->where = array();
        }
        return ($result);
    }
    /**
     * Filter INPUT or GET<BR>
     * This method filters INPUT or GET params
     * Exemple: <i>$bot_value = $this->input("NAME", "POST"); -- Filter as POST</i>
     * Exemple: <i>$bot_value = $this->input("NAME", "GET"); -- Filter as GET</i>
     * Exemple: <i>$bot_value = $this->input("NAME", "GET_POST"); -- Try to filter as POST if nothing is found try to filter as GET </i>
     * Exemple: <i>$bot_value = $this->input("NAME", "POST_GET"); -- Try to filter as GET if nothing is found try to filter as POST </i>
     * @param String $VF_name name Param GET or POST
     * @param String $VF_type type of filter (POST, GET, GET_POST, POST_GET)
     * @param String $VF_filter type of filter (check PHP documentation of 'filter_input')
     * @return String if nothing is found it returns false else it return a value it can be (String, Boolean, Integer...)
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
    protected function isSessionSet(string $name): bool
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
    protected function setSession(string $name, $value): Model
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
    protected function unsetSession(string $name): bool
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

    /**
     * Load a view file<BR>
     * This method can return a include view or html of itself
     * Example: <i>$this->view("basic", array('ID', 1), false);</i>
     * Example: <i>$bot_html = $this->view("basic", array('ID', 1), true);</i>
     * @param String $VF_name_view name of views file
     * @param Array $VF_data array with data that you want to send to view
     * @param Boolean $VF_mode set if you want to require (false) or html (true) of view`s called
     * @return Object if file does not found it returns false else if param 3 is false it requires the view else if param 3 is true it returns the html of this view
     */
    protected function view(string $viewOrFolder = "", string $view = null, array $data = array(), bool $htmlReturn = true)
    {
        if (is_array($data) and count($data) > 0) {
            extract($data, EXTR_PREFIX_SAME, "app");
        }
        $viewPath = $this->vf->appView . $viewOrFolder . '.php';
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
        return false;
    }
}