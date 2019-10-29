<?php

/**
 * @author: awei.tian
 * @date: 2013-11-9
 * @function:
 */
// show table status from amzphp where name='ebank';
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+
// | Name | Engine | Version | Row_format | Rows | Avg_row_length | Data_length |Max_data_length | Index_length | Data_free | Auto_increment | Create_time | Update_time | Check_time | Collation | Checksum | Create_options | Comment |
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+
// | ebank | MyISAM | 10 | Dynamic | 3 | 1586 | 4760 |281474976710655 | 2048 | 0 | 4 | 2013-09-14 14:37:58 | 2013-09-14 14:44:19 | NULL | utf8_unicode_ci | NULL | | 电子银行 |
// +-------+--------+---------+------------+------+----------------+-------------+-----------------+--------------+-----------+----------------+---------------------+---------------------+------------+-----------------+----------+----------------+----------+

// SHOW COLUMNS FROM device_info
// +-------+-------------+------+-----+---------+----------------+
// | Field | Type | Null | Key | Default | Extra |
// +-------+-------------+------+-----+---------+----------------+
// | sid | int(11) | NO | PRI | NULL | auto_increment |
// | vv | varchar(50) | YES | | NULL | |
// +-------+-------------+------+-----+---------+----------------+

// Field Type Collation Null Key Default Extra Privileges Comment
// ------ ---------------- --------------- ------ ------ ------- ------ ------------------------------- ---------
// pk1 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
// pk2 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
// data varchar(10) utf8_general_ci YES (NULL) select,insert,update,references

// key_descriptions的结构为
// field => pk1 int(10) unsigned (NULL) NO PRI (NULL) select,insert,update,references
namespace Aw\Db\Reflection\Mysql;

use Aw\Db\Connection\Mysql;
use Aw\Cache\ICache;
use Aw\Db\Reflection\ITableReflection;

class Table implements ITableReflection
{
    /**
     *
     * @var Mysql
     */
    public $connection;
    /**
     *
     * @var ICache
     */
    public $cache;
    /**
     *
     * @var string
     */
    private $tabname;
    /**
     * 初始值 为[],初始化以后为[
     *    tablename => []
     * ]
     *
     * @var array
     */
    private static $tab_descriptions = array();
    /**
     * 初始值 为[],初始化以后为[
     *    tablename => []
     * ]
     *
     * @var array
     */
    private static $col_descriptions = array();

    public function __construct($tabname, Mysql $connection, ICache $cache = null)
    {
        $this->connection = $connection;
        $this->cache = $cache;
        $this->setTableName($tabname);
    }

    public function setTableName($name)
    {
        $this->tabname = $name;
        return $this;
    }

    public function getTableName()
    {
        return $this->tabname;
    }

    public function cacheKeyKeyDesc()
    {
        return 'Tian.MysqlTableReflection.key_descriptions.' . $this->tabname;
    }

    public function cacheKeyDesc()
    {
        return 'Tian.MysqlTableReflection.descriptions.' . $this->tabname;
    }

    /**
     * @return mixed
     */
    public function getPk()
    {
        $this->initTableColDecription();
        $ret = array();
        foreach (self::$col_descriptions [$this->tabname] as $val) {
            if ($val ["Key"] == "PRI")
                $ret [] = $val ["Field"];
        }
        return count($ret) == 1 ? $ret[0] : $ret;
    }

    protected function getAttrs($field)
    {
        $this->initTableColDecription();
        $ret = array();
        foreach (self::$col_descriptions [$this->tabname] as $val) {
            $ret [$val ["Field"]] = $val [$field];
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->getAttrs("Default");
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->getAttrs("Comment");
    }

    /**
     * @return array
     */
    public function getLengths()
    {
        $this->initTableColDecription();
        $ret = array();
        foreach (self::$col_descriptions [$this->tabname] as $val) {
            $ret [$val ["Field"]] = $this->getLen($val ["Field"]);
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $this->initTableColDecription();
        $ret = array();
        foreach (self::$col_descriptions [$this->tabname] as $val) {
            $ret [$val ["Field"]] = $this->getType($val ["Field"]);
        }
        return $ret;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ITableInfo::getColumnNames()
     */
    public function getColumnNames()
    {
        $this->initTableColDecription();
        return array_keys(self::$col_descriptions [$this->tabname]);
    }

    public function getEngineType()
    {
        $this->initTableDecription();
        return self::$tab_descriptions [$this->tabname] ["Engine"];
    }

    public function getTableComment()
    {
        $this->initTableDecription();
        return self::$tab_descriptions [$this->tabname] ["Comment"];
    }

    // filed start

    /**
     * 返回像这样set,enum,binary,int
     * @param $field
     * @return mixed
     */
    public function getType($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["type"];
    }

    public function getLen($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["len"];
    }

    public function isUnsiged($field)
    {
        $this->initTableColDecription();
        $ret = $this->_split_typelen(self::$col_descriptions [$this->tabname] [$field] ["Type"]);
        return $ret ["unsiged"] === true;
    }

    public function isNullField($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Null"] === 'YES';
    }

    public function getDefault($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Default"];
    }

    public function getComment($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Comment"];
    }

    public function isPk($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Key"] === 'PRI';
    }

    public function isAutoIncrement($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Extra"] === 'auto_increment';
    }

    public function isUnique($field)
    {
        $this->initTableColDecription();
        return self::$col_descriptions [$this->tabname] [$field] ["Key"] === 'UNI';
    }

    private function _split_typelen($t)
    {
        if (preg_match("/^[a-z]+$/", $t)) {
            return array(
                'type' => $t,
                'len' => null,
                'unsiged' => null
            );
        } else if (preg_match("/^([a-z]+)\(([^\)]+)\)$/", $t, $matches)) {
            return array(
                'type' => $matches [1],
                'len' => str_replace("'", "", $matches [2]),
                'unsiged' => null
            );
        } else if (preg_match("/^([a-z]+)\(([0-9]+)\) unsigned$/", $t, $matches)) {
            return array(
                'type' => $matches [1],
                'len' => $matches [2],
                'unsiged' => true
            );
        } else {
            return array(
                'type' => null,
                'len' => null,
                'unsiged' => null
            );
        }
    }

    // field end
    protected function initTableDecription()
    {
        if (isset (self::$tab_descriptions [$this->tabname]) && is_array(self::$tab_descriptions [$this->tabname])) {
            return;
        }
        if (!is_null($this->cache)) {
            $ret = $this->cache->get($this->cacheKeyDesc());
            if (is_array($ret)) {
                self::$tab_descriptions [$this->tabname] = $ret;
                return;
            }
        }
        $result = $this->connection->fetch("show table status from `" . $this->connection->getDbName() . "` where name=:tablename", array(
            "tablename" => $this->tabname
        ));
        self::$tab_descriptions [$this->tabname] = $result;
        if (!is_null($this->cache)) {
            $this->cache->set($this->cacheKeyDesc(), $result, 0);
        }
        return;
    }

    protected function initTableColDecription()
    {
        if (isset (self::$col_descriptions [$this->tabname]) && is_array(self::$col_descriptions [$this->tabname])) {
            return;
        }
        if (!is_null($this->cache)) {
            $ret = $this->cache->get($this->cacheKeyKeyDesc());
            if (is_array($ret)) {
                self::$col_descriptions [$this->tabname] = $ret;
                return;
            }
        }
        $result = $this->connection->fetchAll("SHOW FULL COLUMNS FROM `$this->tabname`");
        if (count($result) == 0) {
            self::$col_descriptions [$this->tabname] = array();
            if (!is_null($this->cache)) {
                $this->cache->set($this->cacheKeyKeyDesc(), array(), 0);
            }
            return;
        }

        self::$col_descriptions [$this->tabname] = array_combine($this->array_column($result, 'Field'), $result);
        if (!is_null($this->cache)) {
            $this->cache->set($this->cacheKeyKeyDesc(), self::$col_descriptions [$this->tabname], 0);
        }
    }

    public function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string)$row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}