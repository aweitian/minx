<?php

namespace Aw\Db\Reflection;

interface ITableReflection
{
    /**
     * return array
     */
    function getPk();

    /**
     * @return array
     */
    function getColumnNames();

    /**
     * return string
     */
    function getEngineType();

    /**
     * return string
     */
    function getTableName();

    /**
     * return string
     */
    function getTableComment();

    function getType($field);

    function getLen($field);

    function isUnsiged($field);

    function isNullField($field);

    function getDefault($field);

    function getComment($field);

    function isPk($field);

    function isAutoIncrement($field);

    function isUnique($field);
}