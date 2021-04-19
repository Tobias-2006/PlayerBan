<?php

namespace tobias14\playerban\database;

use Exception;
use mysqli;
use tobias14\playerban\PlayerBan;

/**
 * This class controls the database connection
 *
 * Class DataManager
 * @package tobias14\playerban\database
 */
class DataManager {

    /** @var mysqli $db */
    private $db;
    /** @var PlayerBan $plugin */
    private $plugin;
    /** @var array $settings */
    private $settings;

    /**
     * DataManager constructor.
     *
     * @param PlayerBan $plugin
     * @param array $settings
     */
    public function __construct(PlayerBan $plugin, array $settings) {
        $this->plugin = $plugin;
        $this->settings = $settings;
        try {
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['DataManager'], $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("db.connection.failed"));
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            return;
        }
        $this->init();
    }

    /**
     * Initializes the DataManager
     *
     * @return void
     */
    private function init() {
        $this->db->query("CREATE TABLE IF NOT EXISTS bans(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, PRIMARY KEY(id));");
        $this->db->query("CREATE TABLE IF NOT EXISTS pending(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, duration INT NOT NULL, timestamp INT NOT NULL, moderator VARCHAR(255) NOT NULL, reason TEXT NOT NULL, PRIMARY KEY(id));");
        $this->db->query("CREATE TABLE IF NOT EXISTS punishments(id INT NOT NULL, duration INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $this->db->query("CREATE TABLE IF NOT EXISTS logs(type INT NOT NULL, message TEXT NOT NULL, moderator VARCHAR(255) NOT NULL, timestamp INT NOT NULL);");
    }

    /**
     * Reconnects to the database
     *
     * @return void
     */
    private function reconnect() {
        try {
            $settings = $this->settings;
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['DataManager'], $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("db.connection.failed"));
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
        }
    }

    /**
     * Checks the database connection and restores it in case of errors
     *
     * @return bool
     */
    private function checkConnection() : bool {
        if(!$this->db->ping()) {
            $this->reconnect();
            if($this->db->connect_error != '') {
                $this->plugin->getLogger()->critical($this->db->connect_error);
                if($this->plugin->isEnabled())
                    $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                return false;
            }
        }
        return true;
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $moderator
     * @param int $timestamp
     * @return bool|null
     */
    public function saveLog(int $type, string $message, string $moderator, int $timestamp) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO logs(type, message, moderator, timestamp) VALUES(?, ?, ?, ?);");
        $stmt->bind_param("issi", $type, $message, $moderator, $timestamp);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param int $id
     * @return null|bool
     */
    public function punishmentExists(int $id) : ?bool {
        if(!$this->checkConnection()) return null;
        $query = "SELECT * FROM punishments WHERE id='{$id}'";
        $result = $this->db->query($query);
        return $result->num_rows === 1;
    }

    /**
     * Returns a list of all punishments
     *
     * @return null|array
     */
    public function getAllPunishments() : ?array {
        if(!$this->checkConnection()) return null;
        $result = $this->db->query("SELECT * FROM punishments");
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
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO punishments(id, duration, description) VALUES(?, ?, ?);");
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
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("DELETE FROM punishments WHERE id=?;");
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
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("UPDATE punishments SET duration=?, description=? WHERE id=?;");
        $stmt->bind_param("isi", $duration, $description, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}
