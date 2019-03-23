<?php
/**
 * Created by PhpStorm.
 * User: Freekazoid
 * Date: 21.12.2017
 * Time: 20:13
 */

//@require_once 'config.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'g_word');
define('DB_USER', 'root');
define('DB_PASS', '');
/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'id7870168_g_word');
define('DB_USER', 'id7870168_web26yaru');
define('DB_PASS', 'N3115854n');
*/
define('DB_CHAR', 'utf8');

class DB
{
    protected static $instance = null;

    public function __construct()
    {
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    public static function instance()
    {
        if (self::$instance === null) {
            $opt = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => TRUE,
            );
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $opt);
        }
        return self::$instance;
    }

    public static function run($sql, $args = [])
    {
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }

    public function __clone()
    {
    }
}

?>