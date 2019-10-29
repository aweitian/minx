<?php

namespace Aw\Db\Reflection;

interface IDbReflection
{
    function getDbName();

    function getTableNames();

    /**
     * 包括VIEW
     */
    function getFullTableNames();

    function tableExists($tabname);
}