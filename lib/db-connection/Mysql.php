<?php

/**
 * $links->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
 * 设置为SILENT模式，执行有问题会有WARNNING产生
 */

namespace Aw\Db\Connection;

use PDO;
use Exception;
use PDOStatement;

class Mysql
{
    // 查询语句日志
    protected static $queryLogs = array();
    protected $config;
    protected $mode = PDO::ERRMODE_EXCEPTION;
    public $lastSql = '';
    public $lastBindData = array();
    /**
     * @var PDOStatement
     */
    protected $lastStm = null;

    /**
     *
     * @var \PDO
     */
    protected $pdo;

    protected $last_error_code;

    protected $is_no_data_update = true;

    /**
     * 获取连接
     *
     * @param array $config
     *            [
     *            'host' => 127.0.0.1,
     *            'port' => 3306,
     *            'database' => db,
     *            'user' => user
     *            'password' => pass
     *            'charset' => utf8
     *            ]
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $config = array(), array $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION))
    {
        $this->config = $config;
        $this->chkConf();
        $dsn = 'mysql:host=' . $this->getHost() . ';port=' . $this->getPort();

        if (version_compare(PHP_VERSION, '5.3.6', '<')) {
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->getCharset();
            }
        } else {
            $dsn .= ';charset=' . $this->getCharset();
        }

        $links = new PDO ($dsn, $config ['user'], $config ['password'], $options);
        $links->exec("SET sql_mode = ''");
        $this->pdo = $links;
        if ($options[PDO::ATTR_ERRMODE] == PDO::ERRMODE_SILENT) {
            $this->setSilentMode();
        }
        if ($this->getDbName() != null) {
            $this->useDb($this->config['database']);
        }
    }

    /**
     * @param $db
     * @return $this
     */
    public function useDb($db)
    {
        $this->config['database'] = $db;
        $this->pdo->exec("use `$db`");
        return $this;
    }

    /**
     * 异常由MysqlPdoConn对象抛出
     */
    public function setSilentMode()
    {
        $this->mode = PDO::ERRMODE_SILENT;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    /**
     * 异常由PDO对象抛出
     */
    public function setExceptionMode()
    {
        $this->mode = PDO::ERRMODE_EXCEPTION;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 设置为TRUE。异常由PDO对象抛出
     * 默认为FALSE，异常由MysqlPdoConn对象抛出
     * @param $mode
     */
    public function setDebugMode($mode)
    {
        if ($mode) {
            $this->mode = PDO::ERRMODE_EXCEPTION;
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            $this->mode = PDO::ERRMODE_SILENT;
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        }
    }

    /**
     * @throws Exception
     */
    private function chkConf()
    {
        $f = true;
        $f = $f && is_array($this->config);
        $f = $f && array_key_exists('host', $this->config);
        $f = $f && array_key_exists('port', $this->config);
        //$f = $f && array_key_exists('database', $this->config);
        $f = $f && array_key_exists('user', $this->config);
        $f = $f && array_key_exists('password', $this->config);
        $f = $f && array_key_exists('charset', $this->config);
        if (!$f) {
            throw new \Exception ('Malformed config.' . var_export($this->config, true) . ' --- 
				host => 127.0.0.1,
				port => 3306,
				database => db, (optional)
				user => user
				password => pass
				charset => utf8
				');
        }
    }

    public function getDbName()
    {
        return isset($this->config ['database']) ? $this->config ['database'] : null;
    }

    public function getHost()
    {
        return $this->config ['host'];
    }

    public function getPort()
    {
        return $this->config ['port'];
    }

    public function getCharset()
    {
        return $this->config ['charset'];
    }

    public function reset()
    {
        $this->lastStm = null;
        $this->lastBindData = array();
        $this->lastSql = '';
        $this->is_no_data_update = true;
    }

    protected function errno($code)
    {
        if (preg_match("/^\d+$/", $code)) {
            $err = $code;
        } else {
            $err = 500;
        }
        return $err;
    }

    /**
     *
     * 返回插入ID
     *
     * @param string $sql
     * @param array $data
     * @param array $bindType
     *            KEY和DATA一样，值为PDO:PARAM_*
     * @return int
     * @throws Exception
     */
    public function insert($sql, $data = array(), $bindType = array())
    {
        self::$queryLogs [] = $sql . " " . var_export($data, true);
        $this->reset();
        $sth = $this->pdo->prepare($sql);
        if (!$sth) {
            $error = $sth->errorInfo();
            throw new Exception ($sql . " ;BindParams:" . var_export($data, true) . implode(';', $error));
        }
        foreach ($data as $k => $v) {
            $sth->bindValue($k, $v, array_key_exists($k, $bindType) ? $bindType [$k] : \PDO::PARAM_STR);
        }
        if (@$sth->execute()) {
            $id = $this->pdo->lastInsertId();
            $this->lastStm = $sth;
            return $id;
        } else {
            $this->lastSql = $sql;
            $this->lastBindData = $data;
            if ($this->mode == PDO::ERRMODE_SILENT) {
                $error = $sth->errorInfo();
                $this->last_error_code = $error[0];
                throw new Exception (
                    $sql . " ;BindParams:" . var_export($data, true) . implode(';', $error),
                    $this->errno($error[0])
                );
            }
        }
    }

    /**
     * @param $sql
     * @param array $data
     * @param array $bindType
     * @return mixed|null
     * @throws Exception
     */
    public function scalar($sql, $data = array(), $bindType = array())
    {
        $this->reset();
        $sth = $this->pdo->prepare($sql);
        self::$queryLogs [] = $sql . " " . var_export($data, true);
        if (!$sth) {
            $error = $sth->errorInfo();
            throw new Exception ($sql . " ;BindParams:" . var_export($data, true) . implode(';', $error));
        }
        foreach ($data as $k => $v) {
            $sth->bindValue($k, $v, array_key_exists($k, $bindType) ? $bindType [$k] : \PDO::PARAM_STR);
        }
        $sth->setFetchMode(\PDO::FETCH_NUM);
        if (@$sth->execute()) {
            $ret = $sth->fetch();
            $this->lastStm = $sth;
            if (!is_array($ret))
                return null;
            return $ret[0];
        }
        $this->lastSql = $sql;
        $this->lastBindData = $data;
        if ($this->mode == PDO::ERRMODE_SILENT) {
            $error = $sth->errorInfo();
            $this->last_error_code = $error[0];
            throw new Exception (
                $sql . " ;BindParams:" . var_export($data, true) . implode(';', $error),
                $this->errno($error[0])
            );
        }

    }

    /**
     *
     * 返回一维数组,SQL中的结果集中的第一个元组
     *
     * @param string $sql
     * @param array $data
     * @param array $bindType
     * @param int $fetch_mode
     * @return array ;
     * @throws Exception
     */
    public function fetch($sql, $data = array(), $bindType = array(), $fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->reset();
        $sth = $this->pdo->prepare($sql);
        self::$queryLogs [] = $sql . " " . var_export($data, true);
        if (!$sth) {
            $error = $sth->errorInfo();
            throw new Exception ($sql . " ;BindParams:" . var_export($data, true) . implode(';', $error));
        }
        foreach ($data as $k => $v) {
            $sth->bindValue($k, $v, array_key_exists($k, $bindType) ? $bindType [$k] : \PDO::PARAM_STR);
        }
        $sth->setFetchMode($fetch_mode);
        if (@$sth->execute()) {
            $ret = $sth->fetch();
            $this->lastStm = $sth;
            if (!is_array($ret))
                return array();
            return $ret;
        }
        $this->lastSql = $sql;
        $this->lastBindData = $data;
        if ($this->mode == PDO::ERRMODE_SILENT) {
            $error = $sth->errorInfo();
            $this->last_error_code = $error[0];
            throw new Exception (
                $sql . " ;BindParams:" . var_export($data, true) . implode(';', $error),
                $this->errno($error[0])
            );
        }
    }

    /**
     *
     * 返回二维数组
     *
     * @param string $sql
     * @param array $data
     * @param array $bindType
     * @param int $fetch_mode
     * @return array ;
     * @throws Exception
     */
    public function fetchAll($sql, $data = array(), $bindType = array(), $fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->reset();
        $sth = $this->pdo->prepare($sql);
        self::$queryLogs [] = $sql . " " . var_export($data, true);
        if (!$sth) {
            $error = $sth->errorInfo();
            throw new Exception ($sql . " ;BindParams:" . var_export($data, true) . implode(';', $error));
        }
        foreach ($data as $k => $v) {
            $sth->bindValue($k, $v, array_key_exists($k, $bindType) ? $bindType [$k] : \PDO::PARAM_STR);
        }
        $sth->setFetchMode($fetch_mode);
        if (@$sth->execute()) {
            $r = $sth->fetchAll();
            $this->lastStm = $sth;
            return $r;
        }
        $this->lastSql = $sql;
        $this->lastBindData = $data;
        $this->lastStm = $sth;
        if ($this->mode == PDO::ERRMODE_SILENT) {
            $error = $sth->errorInfo();
            $this->last_error_code = $error[0];
            throw new Exception (
                $sql . " ;BindParams:" . var_export($data, true) . implode(';', $error),
                $this->errno($error[0])
            );
        }
    }

    /**
     *
     * 返回影响行数
     *
     * @param string $sql
     * @param array $data
     * @param array $bindType
     *            KEY和DATA一样，值为PDO:PARAM_*
     * @return int
     * @throws Exception
     */
    public function exec($sql, $data = array(), $bindType = array())
    {
        $this->reset();
        $sth = $this->pdo->prepare($sql);
        if (!$sth) {
            $error = $sth->errorInfo();
            throw new Exception ($sql . " ;BindParams:" . var_export($data, true) . implode(';', $error));
        }
        foreach ($data as $k => $v) {
            $sth->bindValue($k, $v, array_key_exists($k, $bindType) ? $bindType [$k] : \PDO::PARAM_STR);
        }
        if (@$sth->execute()) {
            $this->lastStm = $sth;
            $r = $sth->rowCount();
            if ($r == 0) {
                $this->is_no_data_update = true;
            } else {
                $this->is_no_data_update = false;
            }
            return $r;
        } else {
            $this->lastSql = $sql;
            $this->lastBindData = $data;
            if ($this->mode == PDO::ERRMODE_SILENT) {
                $error = $sth->errorInfo();
                $this->last_error_code = $error[0];
                throw new Exception (
                    $sql . " ;BindParams:" . var_export($data, true) . implode(';', $error),
                    $this->errno($error[0])
                );
            }
            $this->is_no_data_update = true;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isDuplicateEntry()
    {
        if ($this->mode != PDO::ERRMODE_SILENT) {
            throw new Exception('只能在 ERRMODE_SILENT 模式来检测');
        }
        return $this->last_error_code == '23000';
    }

    /**
     * @return bool
     */
    public function isNoDataUpdate()
    {
        return $this->is_no_data_update;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isBindParaError()
    {
        if ($this->mode != PDO::ERRMODE_SILENT) {
            throw new Exception('只能在 ERRMODE_SILENT 模式来检测');
        }
        return $this->last_error_code == 'HY093';
    }

    public function closeStm()
    {
        if ($this->lastStm) {
            $this->lastStm->closeCursor();
        }
    }


    /**
     * 执行事务处理
     *
     * @param \Closure $closure
     *
     * @return $this
     */
    public function transaction(\Closure $closure)
    {
        try {
            $this->beginTransaction();
            // 执行事务
            $closure ();
            $this->commit();
        } catch (Exception $e) {
            // 回滚事务
            $this->rollback();
        }
        return $this;
    }

    /**
     * 开启一个事务
     *
     * @return $this
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    /**
     * 开启事务
     *
     * @return $this
     */
    public function rollback()
    {
        $this->pdo->rollback();
        return $this;
    }

//    /**
//     * @return PDO
//     */
//    public function getPdo()
//    {
//        return $this->pdo;
//    }

    /**
     * 开启事务
     *
     * @return $this
     */
    public function commit()
    {
        $this->pdo->commit();
        return $this;
    }

    /**
     * 获得查询SQL语句
     *
     * @return array
     */
    public function getQueryLog()
    {
        return self::$queryLogs;
    }
}