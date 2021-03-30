<?php

namespace tobias14\playerban\database;

use Exception;
use mysqli;
use tobias14\playerban\PlayerBan;

/**
 * This class controls the database connection
 *
 * Class Database
 * @package tobias14\playerban\database
 */
class Database {

    /**
     * TODO: Specify tables creation correctly
     *
     * @return void
     */
    public static function init() {
        $database = self::connect();
        $database->query("CREATE TABLE IF NOT EXISTS users()");
        $database->query("CREATE TABLE IF NOT EXISTS pending()");
        $database->query("CREATE TABLE IF NOT EXISTS logs()");
    }

    /**
     * @return mysqli|null
     */
    public static function connect() : ?mysqli {
        $config = PlayerBan::getInstance()->getConfig();
        try {
            $connection = new mysqli(
                    $config->get("host", "127.0.0.1"),
                    $config->get("username", "root"),
                    $config->get("passwd", "password"),
                    $config->get("dbname", "PlayerBan"),
                    $config->get("port", 3306));
        } catch (Exception $e) {
            $connection = null;
        }
        return $connection;
    }

    /**
     * Checks the connection to the database
     *
     * @return bool
     */
    public static function checkConnection() : bool {
        $connection = self::connect();
        $state = true;
        if(is_null($connection)) {
            $state = false;
        }
        $connection->close();
        return $state;
    }

}
