<?php

namespace Aw\Db\Reflection\Mysql;
use Aw\Db\Connection\Mysql;
use Aw\Cache\ICache;
use Aw\Db\Reflection\IDbReflection;

class Db implements IDbReflection {
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
    private static $descriptions = null;
    public function __construct(Mysql $connection, ICache $cache = null) {
        $this->cache = $cache;
        $this->connection = $connection;
        if (is_null ( self::$descriptions ))
            self::$descriptions = $this->getDescription ();
    }
    public function getDbName() {
        return $this->connection->getDbName ();
    }
    public function getCharset() {
        return $this->connection->getCharset ();
    }
    public function getHost() {
        return $this->connection->getHost ();
    }
    public function getTableNames() {
        $ret = array ();
        foreach ( self::$descriptions as $data ) {
            if ($data ["Comment"] !== "VIEW")
                $ret [] = $data ["Name"];
        }
        return $ret;
    }
    public function getRawDescription() {
        return self::$descriptions;
    }
    /**
     * 包括VIEW
     */
    public function getFullTableNames() {
        $ret = array ();
        foreach ( self::$descriptions as $data ) {
            $ret [] = $data ["Name"];
        }
        return $ret;
    }

    /**
     * @param $tabname
     * @return bool
     */
    public function tableExists($tabname) {
        $hash = $this->getTableNames ();
        return in_array ( $tabname, $hash );
    }

    protected function getDescription() {
        if (! is_null ( $this->cache )) {
            $ret = $this->cache->get ( 'dbflection.alldescription' );
            if (is_array ( $ret )) {
                return $ret;
            }
        }
        $ret = $this->connection->fetchAll ( "SHOW TABLE status FROM `" . $this->connection->getDbName () . "`" );
        if (! is_null ( $this->cache )) {
            $this->cache->set ( 'dbflection.alldescription', $ret, 0 );
        }
        return $ret;
    }
}