<?php

namespace VF;

if (!defined('PATH_VF')) { // DO NOT REMOVE
    exit('VF IS NOT LOADED');
}


//Result Class

class Result
{
    public VFphp $vf;
    public bool $success = false;
    public ?string $error = null;
    private int $lastId = 0;
    private ?\PDOStatement $returnsDb;
    private ?string $debug = null;

    /**
     * Constructor method
     *
     * @param VFphp $vf
     * @param \PDOStatement|null $returnsDb
     * @param integer $lastId
     * @param string $debug
     * @param string|null $error
     */
    public function __construct(VFphp $vf, \PDOStatement $returnsDb = null, int $lastId, string $debug = null, string $error = null)
    {
        $this->lastId = $lastId;
        $this->returnsDb = $returnsDb;
        $this->debug = $debug;
        $this->success =  $error == null;
        $this->error = $error;
        $this->vf = $vf;
    }

    /**
     * get result count from query<BR>
     * Exemple: <i>$bot_queryNum = $this->get_count();</i>
     * @return Integer number of query result
     */
    public function count()
    {
        if (is_object($this->returnsDb)) {
            $countRows = $this->returnsDb->rowCount();
            return $countRows;
        }
        return 0;
    }
    /**
     * get result as object<BR>
     * Exemple: <i>$bot_queryNum = $this->get_fetch();</i>
     * @return array array as rows, object as row
     */
    public function fetch($array = false): array
    {
        if (is_object($this->returnsDb)) {
            if ($array) {
                $fetchRows = $this->returnsDb->fetchAll(\PDO::FETCH_ASSOC);
                return $fetchRows;
            }
            $fetchRows = $this->returnsDb->fetchAll(\PDO::FETCH_CLASS);
            return $fetchRows;
        }
        return [];
    }

    /**
     * get the last result as object<BR>
     * Exemple: <i>$bot_queryNum = $this->get_row();</i>
     * @return Object result (only 1 result)
     */
    public function row($array = false)
    {
        if (is_object($this->returnsDb)) {
            if ($array) {
                $row = $this->returnsDb->fetch(\PDO::FETCH_ASSOC);
                return $row;
            }
            $row = $this->returnsDb->fetch(\PDO::FETCH_OBJ);
            return $row;
        }
        return null;
    }

    /**
     * get the last ID inserted<BR>
     * Exemple: <i>$bot_query = $this->get_last_id();</i>
     * @return Integer last ID inserted
     */
    public function getLastID()
    {
        return $this->lastId;
    }
    /**
     * get last query sent to database<BR>
     * Exemple: <i>$bot_query = $this->debug_query();</i>
     * @return String last query
     */
    public function getQuery()
    {
        return $this->debug;
    }
    public function super()
    {
        return $this->returnsDb;
    }
}
