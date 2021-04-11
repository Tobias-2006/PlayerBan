<?php

namespace tobias14\playerban\database;

use Exception;
use mysqli;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\tasks\ConnectionTerminationTask as CT;

/**
 * This class controls the database connection
 *
 * Class Database
 * @package tobias14\playerban\database
 */
class Database {

    /** @var mysqli $connection */
    private $connection;
    /** @var PlayerBan $plugin */
    private $plugin;
    /** @var array $settings */
    private $settings;

    /**
     * Database constructor.
     *
     * @param PlayerBan $plugin
     * @param array $settings
     */
    public function __construct(PlayerBan $plugin, array $settings) {
        $this->plugin = $plugin;
        $this->settings = $settings;
        try {
            $this->connection = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("db.connection.failed"));
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            return;
        }
        $this->init();
    }

    /**
     * TODO: Create log functionality
     *
     * @return void
     */
    private function init() {
        $database = $this->connection;
        $database->query("CREATE TABLE IF NOT EXISTS bans(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, PRIMARY KEY(id));");
        $database->query("CREATE TABLE IF NOT EXISTS pending(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, moderator VARCHAR(255) NOT NULL, reason TEXT NOT NULL, PRIMARY KEY(id));");
        $database->query("CREATE TABLE IF NOT EXISTS punishments(id INT NOT NULL, duration INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        //$database->query("CREATE TABLE IF NOT EXISTS logs();");
    }

    /**
     * @return bool
     */
    private function ping() : bool {
        if(!$this->connection) return false;
        return $this->connection->ping();
    }

    /**
     * Close database connection
     *
     * @return void
     */
    public function close() {
        if(!$this->connection) return;
        $this->connection->close();
    }

    /**
     * Reconnects to the database
     *
     * @return bool
     */
    private function reconnect() : bool {
        $this->close();
        try {
            $settings = $this->settings;
            $this->connection = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], $settings['Port']);
            return true;
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("db.connection.failed"));
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            return false;
        }
    }

    /**
     * Checks the database connection and restores it in case of errors
     *
     * @return bool
     */
    private function isAlive() : bool {
        if(!$this->ping()) {
            if(!$this->reconnect()) return false;
        }
        $this->updateCTTask();
        return true;
    }

    /**
     * Close the database connection if it has been unused for 60 seconds.
     *
     * @return void
     */
    private function updateCTTask() {
        if(is_null(CT::getInstance())) {
            $this->plugin->getScheduler()->scheduleRepeatingTask(new CT($this->plugin), 20);
            return;
        }
        CT::getInstance()->timer = 60;
    }

    /**
     * @param int $id
     * @return null|bool
     */
    public function punishmentExists(int $id) : ?bool {
        if(!$this->isAlive()) return null;
        $query = "SELECT * FROM punishments WHERE id='{$id}'";
        $result = $this->connection->query($query);
        return $result->num_rows === 1;
    }

    /**
     * Returns a list of all punishments (max. 25)
     *
     * @return null|array
     */
    public function getAllPunishments() : ?array {
        if(!$this->isAlive()) return null;
        $result = $this->connection->query("SELECT * FROM punishments LIMIT 25");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return null|bool
     */
    public function savePunishment(int $id, int $duration, string $description) : ?bool {
        if(!$this->isAlive()) return null;
        $stmt = $this->connection->prepare("INSERT INTO punishments(id, duration, description) VALUES(?, ?, ?);");
        $stmt->bind_param("iis", $id, $duration, $description);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param int $id
     * @return null|bool
     */
    public function deletePunishment(int $id) : ?bool {
        if(!$this->isAlive()) return null;
        $stmt = $this->connection->prepare("DELETE FROM punishments WHERE id=?;");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return null|bool
     */
    public function updatePunishment(int $id, int $duration, string $description) : ?bool {
        if(!$this->isAlive()) return null;
        $stmt = $this->connection->prepare("UPDATE punishments SET duration=?, description=? WHERE id=?;");
        $stmt->bind_param("isi", $duration, $description, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}
