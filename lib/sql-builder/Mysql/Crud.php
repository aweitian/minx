<?php

/**
 * 2017/5/16 17:39:34
 * 数据库 - SQL语句构建类 - 抽象类
 * 这里只是提供常用的SQL构建，复杂的SQL直接使用CONNECTION类
 * WHERE条件是一个树，条件在叶子节点上，非叶子节点为 OR AND
 */

namespace Aw\Build\Mysql;

class Crud
{
    const DEFAULT_BIND_VALUE = false;
    protected $table;
    protected $expr = array();
    /**
     * 删除的时候，先把要删除的SQL片断中的:AA，：BBB获取，然后删除VALUE中的相应值
     * aa => 'normal'
     * bb => null 从数据源填充
     * @var array
     */
    protected $value = array();
    protected $where_condition = ' AND ';

    protected $use_calc_found_rows = false;

    /**
     *
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @return $this
     */
    public function useCalcFoundRows()
    {
        $this->use_calc_found_rows = true;
        return $this;
    }

    public function disableUseCalcFoundRows()
    {
        $this->use_calc_found_rows = false;
        return $this;
    }

    /**
     * 使用GET POST数据填充绑定数据
     * @param $data_src
     * @param bool $override 是否覆盖不为FALSE的数据
     * @param bool $empty_override POST数据为空是否覆盖
     */
    public function fillDataSrc($data_src, $override = true, $empty_override = true)
    {
        foreach ($this->value as $key => $value) {
            if (isset($data_src[$key])) {
                if ($this->value[$key] === self::DEFAULT_BIND_VALUE) {
                    if ($override) {
                        if ($data_src[$key] === "") {
                            if ($empty_override) {
                                $this->value[$key] = $data_src[$key];
                            }
                        } else {
                            $this->value[$key] = $data_src[$key];
                        }
                    }
                }
            }
        }
    }

    public function getBindValue()
    {
        return $this->value;
    }

    /**
     * 在 useCalcFoundRows()  后作用
     * @return string
     */
    public function count()
    {
        return 'SELECT FOUND_ROWS()';
    }

    /**
     *
     * @return string
     */
    public function select()
    {
        return strtr('SELECT' . ($this->use_calc_found_rows ? ' SQL_CALC_FOUND_ROWS' : '') . ' field FROM table join where groupBy having orderBy limit lock', array(
            'field' => $this->parseField(),
            'table' => $this->parseTable(),
            ' join' => $this->parseJoin(),
            ' where' => $this->parseWhere(),
            ' groupBy' => $this->parseGroupBy(),
            ' having' => $this->parseHaving(),
            ' orderBy' => $this->parseOrderBy(),
            ' limit' => $this->parseLimit(),
            ' lock' => $this->parseLock()
        ));
    }

    /**
     *
     * @return $this
     */
    public function andWhere()
    {
        $this->where_condition = ' AND ';
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function orWhere()
    {
        $this->where_condition = ' OR ';
        return $this;
    }

    /**
     *
     * @return string
     */
    public function delete()
    {
        // 判断是单表还是多表
        $table = $this->parseTable();
        if (strpos($table, ',') !== false) {
            return strtr("DELETE FROM table using where orderBy limit", array(
                'table' => $table,
                ' using' => $this->parseUsing(),
                ' where' => $this->parseWhere(),
                ' orderBy' => '',
                ' limit' => ''
            ));
        } else {
            return strtr("DELETE FROM table using where orderBy limit", array(
                'table' => $table,
                ' using' => $this->parseUsing(),
                ' where' => $this->parseWhere(),
                ' orderBy' => $this->parseOrderBy(),
                ' limit' => $this->parseLimit()
            ));
        }
    }

    /**
     *
     * @return string
     */
    public function insert()
    {
        return strtr("INSERT INTO table (field) VALUES (values)", array(
            'table' => $this->parseTable(),
            'field' => $this->parseField(),
            'values' => $this->parseValues()
        ));
    }

    /**
     *
     * @return string
     */
    public function update()
    {
        return strtr("UPDATE table set where orderBy limit", array(
            'table' => $this->parseTable(),
            'set' => $this->parseSet(),
            ' where' => $this->parseWhere(),
            ' orderBy' => $this->parseOrderBy(),
            ' limit' => $this->parseLimit()
        ));
    }

    /**
     *
     * @return string
     */
    public function replace()
    {
        return strtr("REPLACE INTO table (field) VALUES (values)", array(
            'table' => $this->parseTable(),
            'field' => $this->parseField(),
            'values' => $this->parseValues()
        ));
    }

    /**
     * @param $str
     * @param $field
     * @return array
     */
    protected function parseBindKey($str, $field)
    {
        if (preg_match_all("/:(\w+)/", $str, $m)) {
            return $m[1];
        }
        return array();
    }

    /**
     * 如果ID存在，使用ID，
     * 不存在，
     *      如果FIELD是普通字段名，使用FIELD
     *      如果FIELD中包含了:KEY形式，使用KEY
     *      否则使用NULL
     * @param $field
     * @param $id
     * @return mixed
     */
    protected function getKeyFromField($field, $id)
    {
        if (is_null($id)) {
            if (preg_match("/^\w+$/", $field)) {
                $id = $field;
            } else {
                if (preg_match_all("/:(\w+)/", $field, $m) == 1) {
                    $id = $m[1][0];
                }
            }
        }
        return $id;
    }

    /**
     *
     * @param string $field
     * @param bool|string $value
     * @param string $key
     * @return $this
     */
    public function bindField($field, $key = null, $value = self::DEFAULT_BIND_VALUE)
    {
        return $this->bindExpr('field', $field, $value, $key);
    }

    /**
     *
     * @param string $field
     * @param bool|string $value
     * @param string $key
     * @return $this
     */
    public function bindValues($field, $key = null, $value = self::DEFAULT_BIND_VALUE)
    {
        return $this->bindExpr('values', $field, $value, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindJoin($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('join', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindWhere($expr, $key = null, $bind = array())
    {
        if (preg_match("/^\w+$/", $expr)) {
            $expr = "`$expr` = :$expr";
        }
        return $this->bindExpr('where', $expr, $bind, $key);
    }


    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindRawWhere($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('where', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindGroupBy($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('groupBy', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindHaving($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('having', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindOrderBy($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('orderBy', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindLimit($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('limit', $expr, $bind, $key);
    }

    /**
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindLock($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('lock', $expr, $bind, $key);
    }

    /**
     *
     * @see $this::bindExpr
     *
     * @param string $expr
     * @param array $bind
     * @param string $key
     * @return $this
     */
    public function bindUsing($expr, $key = null, $bind = array())
    {
        return $this->bindExpr('using', $expr, $bind, $key);
    }


    /**
     * @param $name
     * @param $field
     * @return bool
     */
    protected function unBind($name, $field = null)
    {
        if (is_null($field)) {
            if (isset($this->expr [$name])) {
                unset($this->expr [$name]);

                return true;
            }
            return false;
        } else {
            if (isset($this->expr [$name] [$field])) {
                unset($this->expr [$name] [$field]);
                if (isset($this->value[$field])) {
                    unset($this->value [$field]);
                }
                return true;
            }
            return false;
        }
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindField($field)
    {
        return $this->unBind("field", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindValues($field)
    {
        return $this->unBind("values", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindJoin($field)
    {
        return $this->unBind("join", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindWhere($field)
    {
        return $this->unBind("where", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindGroupBy($field)
    {
        return $this->unBind("groupBy", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindHaving($field)
    {
        return $this->unBind("having", $field);
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindOrderBy($field)
    {
        return $this->unBind("orderBy", $field);
    }

    /**
     * @return bool
     */
    public function unBindLimit()
    {
        return $this->unBind("limit");
    }

    /**
     * @return bool
     */
    public function unBindLock()
    {
        return $this->unBind("lock");
    }

    /**
     * @param $field
     * @return bool
     */
    public function unBindUsing($field)
    {
        return $this->unBind("using", $field);
    }

    /**
     * 第二个参数如果是数组，需要第一个长度为BIND KEY,第二个为BIND VALUE
     * 如果第二个是数字或者字符串，则BIND KEY为EXPR,参数二为BIND VALUE
     * 其它只绑定表达表，不绑定值
     * 绑定表达式 bindExpr("where","concat('%',:key,'%')")
     * bind参数为key=>value形式，可以多个
     *
     *
     * @param string $name
     * @param string $expr
     * @param array $bind
     * @param string $id
     * @return $this
     */
    protected function bindExpr($name, $expr, $bind = array(), $id = null)
    {
        $id = $this->getKeyFromField($expr, $id);
        $bind_keys = $this->parseBindKey($expr, $expr);
        if (count($bind_keys) == 1) {
            $this->bindValue($bind_keys[0], is_array($bind) && array_key_exists($bind_keys[0], $bind) ?
                $bind[$bind_keys[0]] : ($bind ? $bind : self::DEFAULT_BIND_VALUE));
        } else {
            foreach ($bind_keys as $bind_key) {
                $this->bindValue($bind_key, is_array($bind) && array_key_exists($bind_key, $bind) ?
                    $bind[$bind_key] : ($bind ? $bind : self::DEFAULT_BIND_VALUE));
            }
        }

        if (!isset ($this->expr [$name]))
            $this->expr [$name] = array();
        if (is_null($id)) {
            $this->expr [$name] [] = $expr;
        } else {
            $this->expr [$name] [$id] = $expr;
        }
        return $this;
    }

    /**
     * 绑定实参 bindValue( "key", "lol")
     * bindValue([])
     *
     * @param string|array $key
     * @param object $val
     * @return $this
     * @internal param string $name
     */
    public function bindValue($key, $val = null)
    {
        if (is_array($key))
            $this->value = array_merge($this->value, $key);
        else if (is_string($key) || is_numeric($key))
            $this->value [$key] = $val;
        return $this;
    }

    protected function getBindExprs($name)
    {
        // support since php 5.3
        return isset ($this->expr [$name]) ? $this->expr [$name] : array();
    }

    /**
     * reset bind values
     *
     * @return $this
     */
    public function reset()
    {
        $this->value = array();
        $this->expr = array();
        $this->where_condition = ' AND ';
        return $this;
    }

    protected function parseTable()
    {
        if (preg_match("/^\w+$/", $this->table)) {
            return "`{$this->table}`";
        }
        return $this->table;
    }

    protected function parseField()
    {
        $expr = $this->getBindExprs('field');
        foreach ($expr as &$item) {
            if (preg_match("/^\w+$/", $item)) {
                $item = "`" . $item . "`";
            }
        }
        return $expr ? implode(',', $expr) : '*';
    }

    protected function parseValues($strict = false)
    {
        $ret = $this->getBindExprs('values');
        if ($strict) {
            return $ret ? implode(',', $ret) : '';
        }
        $fields = $this->getBindExprs('field');
        $vals = array();
        foreach ($fields as $field) {
            if (!array_key_exists($field, $ret)) {
                $vals[$field] = ":$field";
            } else {
                $vals[$field] = $ret[$field];
            }
        }
        return $vals ? implode(',', $vals) : '';
    }

    public function getBindFields()
    {
        return array_keys($this->value);
    }

    protected function parseJoin()
    {
        $expr = $this->getBindExprs('join');
        return $expr ? ' ' . implode(' ', $expr) : '';
    }

    protected function parseWhere()
    {
        if ($expr = $this->getBindExprs('where')) {
            return " WHERE " . implode($this->where_condition, $expr);
        }
        return '';
    }

    protected function parseGroupBy()
    {
        if ($expr = $this->getBindExprs('groupBy')) {
            return " GROUP BY " . implode(',', $expr);
        }
        return '';
    }

    protected function parseHaving()
    {
        if ($expr = $this->getBindExprs('having')) {
            return " HAVING " . current($expr);
        }
        return '';
    }

    protected function parseOrderBy()
    {
        if ($expr = $this->getBindExprs('orderBy')) {
            return " ORDER BY " . implode(',', $expr);
        }
        return '';
    }

    protected function parseLimit()
    {
        if ($expr = $this->getBindExprs('limit')) {
            if (count($expr) == 1) {
                return " LIMIT " . current($expr);
            } else if (count($expr) == 2) {
                return " LIMIT " . current($expr) . "," . end($expr);
            }
        }
        return '';
    }

    protected function parseLock()
    {
        if ($expr = $this->getBindExprs('lock')) {
            return ' ' . current($expr);
        }
        return '';
    }

    protected function parseSet()
    {
        $ret = $this->getBindExprs('values');
        $fields = $this->getBindExprs('field');
        $vals = array();
        foreach ($fields as $field) {
            if (!array_key_exists($field, $ret)) {
                $vals[$field] = "`$field`=:$field";
            } else {
                $vals[$field] = "`" . $field . "`=" . $ret[$field];
            }
        }
        return $vals ? 'SET ' . implode(',', $vals) : '';
    }

    protected function parseUsing()
    {
        if ($expr = $this->getBindExprs('using')) {
            return " USING " . implode(',', $expr);
        }
        return '';
    }
}
