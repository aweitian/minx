<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/24
 * Time: 17:11
 */

namespace Aw\Sql\Mysql;

use Aw\Build\Mysql\Crud;
use Aw\Cache\ICache;
use Aw\Db\Connection\Mysql;
use Aw\Db\Reflection\Mysql\Table;


class Gen
{
    /**
     * @var ICache
     */
    protected $cache = null;

    /**
     * @var Mysql
     */
    protected $con;
    /**
     * @var string
     */
    protected $table;

    /**
     * @var Crud
     */
    protected $curd;
    public function setConnection(Mysql $con)
    {
        $this->con = $con;
        return $this;
    }

    public function setTableName($name)
    {
        $this->table = $name;
        return $this;
    }

    public function setCache(ICache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Crud
     */
    public function getBuilder()
    {
        return $this->curd;
    }

    public function getColumns()
    {
        $db_ref = new Table($this->table, $this->con, $this->cache);;
        return $db_ref->getColumnNames();
    }

    public function select($where = '', $fields = '')
    {
        if (is_string($where)) {
            if ($where)
                $where = explode(',', $where);
            else
                $where = array();
        } else {
            $where = (array)$where;
        }
        $build = new Crud($this->table);
        $this->curd = $build;
        if (is_string($fields)) {
            if ($fields)
                $fields = explode(',', $fields);
            else
                $fields = array();
        } else {
            $fields = (array)$fields;
        }
        foreach ($fields as $field) {
            $build->bindField($field);
        }
        foreach ($where as $item) {
            $build->bindWhere($item);
        }
        return $build->select();
    }

    public function insert($fields = '')
    {
        $db_ref = new Table($this->table, $this->con, $this->cache);;
        $build = new Crud($this->table);
        $this->curd = $build;
        if (is_string($fields)) {
            if ($fields)
                $fields = explode(',', $fields);
            else
                $fields = $db_ref->getColumnNames();
        } else {
            $fields = (array)$fields;
        }
        foreach ($fields as $field) {
            if ($db_ref->isPk($field))
                continue;
            $build->bindField($field);
        }
        return $build->insert();
    }

    public function remove()
    {
        $db_ref = new Table($this->table, $this->con, $this->cache);;
        $build = new Crud($this->table);
        $this->curd = $build;
        $pks = $db_ref->getPk();
        foreach ($pks as $pk) {
            $build->bindWhere($pk);
        }
        return $build->delete();
    }

    public function update($fields = '')
    {
        $db_ref = new Table($this->table, $this->con, $this->cache);;
        $build = new Crud($this->table);
        $this->curd = $build;
        if (is_string($fields)) {
            if ($fields)
                $fields = explode(',', $fields);
            else
                $fields = $db_ref->getColumnNames();
        } else {
            $fields = (array)$fields;
        }
        foreach ($fields as $field) {
            if ($db_ref->isPk($field)) {
                $build->bindWhere($field);
            } else {
                $build->bindField($field);
            }

        }
        return $build->update();
    }
}