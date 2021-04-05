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
     * TODO: Create log functionality
     *
     * @return void
     */
    public static function init() {
        $database = self::connect();
        $database->query("CREATE TABLE IF NOT EXISTS bans(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, PRIMARY KEY(id));");
        $database->query("CREATE TABLE IF NOT EXISTS pending(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, moderator VARCHAR(255) NOT NULL, reason TEXT NOT NULL, PRIMARY KEY(id));");
        $database->query("CREATE TABLE IF NOT EXISTS punishments(id INT NOT NULL, duration INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        //$database->query("CREATE TABLE IF NOT EXISTS logs();");
        $database->close();
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
                    $config->get("dbname", "playerban"),
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
        } else {
            $connection->close();
        }
        return $state;
    }

}
