<?php

namespace tobias14\playerban\database;

use tobias14\playerban\PlayerBan;
use mysqli;
use Exception;

/**
 * Class MysqlManager
 * @package tobias14\playerban\database
 */
class MysqlManager extends DataManager {

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
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("connection.failed"));
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
    protected function init() {
        $this->db->query("CREATE TABLE IF NOT EXISTS bans(id INT AUTO_INCREMENT, target VARCHAR(255) NOT NULL, moderator VARCHAR(255) NOT NULL, expiry_time INT NOT NULL, pun_id INT NOT NULL, creation_time INT NOT NULL, PRIMARY KEY(id));");
        $this->db->query("CREATE TABLE IF NOT EXISTS punishments(id INT NOT NULL, duration INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $this->db->query("CREATE TABLE IF NOT EXISTS logs(type INT NOT NULL, description TEXT NOT NULL, moderator VARCHAR(255) NOT NULL, target VARCHAR(255), creation_time INT NOT NULL);");
    }

    /**
     * Reconnects to the database
     *
     * @return void
     */
    private function reconnect() {
        try {
            $settings = $this->settings;
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLang()->translateString("connection.failed"));
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
     * @return void
     */
    public function close() {
        try {
            $this->db->close();
        } catch (Exception $e) {//NOOP
        }
    }

    /**
     * @param int $type
     * @param string $description
     * @param string $moderator
     * @param int $creationTime
     * @param null|string $target
     * @return bool|null
     */
    public function saveLog(int $type, string $description, string $moderator, int $creationTime, string $target = null) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO logs(type, description, moderator, target, creation_time) VALUES(?, ?, ?, ?, ?);");
        $stmt->bind_param("isssi", $type, $description, $moderator, $target, $creationTime);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param int $site
     * @param int $limit
     * @return array|null
     */
    public function getLogs(int $site = 0, int $limit = 6): ?array {
        if(!$this->checkConnection()) return null;
        $site *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM logs ORDER BY creation_time DESC LIMIT ?, ?;");
        $stmt->bind_param("ii",$site, $limit);
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    /**
     * @param int $limit
     * @return int|null
     */
    public function getMaxLogPage(int $limit = 6) : ?int {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM logs");
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $rowCount = $result->fetch_row()[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return $sites;
    }

    /**
     * @param int $id
     * @return null|bool
     */
    public function punishmentExists(int $id) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id=?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows === 1;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getPunishment(int $id) : ?array {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id=?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
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

    /**
     * @param string $target
     * @return bool|null
     */
    public function isBanned(string $target) : ?bool {
        if(!$this->checkConnection()) return null;
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=? AND expiry_time > ?;");
        $stmt->bind_param("si", $target, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows === 1;
    }

    /**
     * @param string $target
     * @param string $moderator
     * @param int $expiryTime
     * @param int $punId
     * @param int $creationTime
     * @return null|bool
     */
    public function saveBan(string $target, string $moderator, int $expiryTime, int $punId, int $creationTime) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO bans(target, moderator, expiry_time, pun_id, creation_time) VALUES(?, ?, ?, ?, ?);");
        $stmt->bind_param("ssiii", $target, $moderator, $expiryTime, $punId, $creationTime);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return bool|null
     */
    public function removeBan(string $target) : ?bool {
        if(!$this->checkConnection()) return null;
        $time = time();
        $stmt = $this->db->prepare("UPDATE bans SET expiry_time=? WHERE target=? AND expiry_time > ?;");
        $stmt->bind_param("isi", $time, $target, $time);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return array|null
     */
    public function getBanByName(string $target) : ?array {
        if(!$this->checkConnection()) return null;
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=? AND expiry_time > ?;");
        $stmt->bind_param("si", $target, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    /**
     * @param int $site
     * @param int $limit
     * @return array|null
     */
    public function getAllCurrentBans(int $site = 0, int $limit = 6) : ?array {
        if(!$this->checkConnection()) return null;
        $time = time();
        $site *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE expiry_time > ? ORDER BY creation_time DESC LIMIT ?, ?");
        $stmt->bind_param("iii", $time, $site, $limit);
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    /**
     * @param int $limit
     * @return int|null
     */
    public function getMaxBanPage(int $limit = 6) : ?int {
        if(!$this->checkConnection()) return null;
        $time = time();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bans WHERE expiry_time > ?");
        $stmt->bind_param("i", $time);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowCount = $result->fetch_row()[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return $sites;
    }

}
