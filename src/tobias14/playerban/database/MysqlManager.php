<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use tobias14\playerban\ban\Ban;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;
use mysqli;
use Exception;
use tobias14\playerban\punishment\Punishment;

class MysqlManager extends DataManager {

    /** @var mysqli $db */
    protected $db;

    /**
     * DataManager constructor.
     *
     * @param PlayerBan $plugin
     * @param string[] $settings
     */
    public function __construct(PlayerBan $plugin, array $settings) {
        $this->plugin = $plugin;
        $this->settings = $settings;
        try {
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], (int) $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLanguage()->translateString("connection.failed"));
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            return;
        }
        $this->init();
    }

    /**
     * @return void
     */
    protected function init() : void {
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
            $this->db = new mysqli($settings['Host'], $settings['Username'], $settings['Password'], $settings['Database'], (int) $settings['Port']);
        } catch (Exception $e) {
            $this->plugin->getLogger()->critical($this->plugin->getLanguage()->translateString("connection.failed"));
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
    public function close() : void {
        try {
            $this->db->close();
        } catch (Exception $e) {//NOOP
        }
    }

    /**
     * @param Log $log
     * @return bool|null
     */
    public function saveLog(Log $log) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO logs(type, description, moderator, target, creation_time) VALUES(?, ?, ?, ?, ?);");
        if(!$stmt) return false;
        $timestamp = time();
        $stmt->bind_param("isssi", $log->type, $log->description, $log->moderator, $log->target, $timestamp);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param Log $log
     * @return bool|null
     */
    public function deleteLog(Log $log): ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("DELETE FROM logs WHERE moderator=? AND creation_time=?;");
        if(!$stmt) return false;
        $stmt->bind_param("si", $log->moderator, $log->creationTime);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return Log[]|null
     */
    public function getLogs(int $page = 0, int $limit = 6): ?array {
        if(!$this->checkConnection()) return null;
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM logs ORDER BY creation_time DESC LIMIT ?, ?;");
        if(!$stmt) return null;
        $stmt->bind_param("ii", $page, $limit);
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new Log((int) $row['type'], $row['description'], $row['moderator'], $row['target'], (int) $row['creation_time']);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM logs;");
        if(!$stmt) return null;
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $rowCount = $result->fetch_row()[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return (int) floor($sites);
    }

    /**
     * @param int $id
     * @return null|bool
     */
    public function punishmentExists(int $id) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id=?;");
        if(!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if(!$result) return null;
        return $result->num_rows === 1;
    }

    /**
     * @param int $id
     * @return Punishment|null
     */
    public function getPunishment(int $id) : ?Punishment {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id=?;");
        if(!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result) return null;
        $result = $result->fetch_assoc();
        if(is_null($result)) return null;
        $stmt->close();
        return new Punishment((int) $result['id'], (int) $result['duration'], $result['description']);
    }

    /**
     * Returns a list of all punishments
     *
     * @return Punishment[]|null
     */
    public function getAllPunishments() : ?array {
        if(!$this->checkConnection()) return null;
        $result = $this->db->query("SELECT * FROM punishments;");
        if(!$result or $result === true) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new Punishment((int) $row['id'], (int) $row['duration'], $row['description']);
        }
        return $data;
    }

    /**
     * @param Punishment $punishment
     * @return null|bool
     */
    public function savePunishment(Punishment $punishment) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO punishments(id, duration, description) VALUES(?, ?, ?);");
        if(!$stmt) return false;
        $stmt->bind_param("iis", $punishment->id, $punishment->duration, $punishment->description);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param Punishment $punishment
     * @return null|bool
     */
    public function deletePunishment(Punishment $punishment) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("DELETE FROM punishments WHERE id=?;");
        if(!$stmt) return false;
        $stmt->bind_param("i", $punishment->id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param Punishment $punishment
     * @return null|bool
     */
    public function updatePunishment(Punishment $punishment) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("UPDATE punishments SET duration=?, description=? WHERE id=?;");
        if(!$stmt) return false;
        $stmt->bind_param("isi", $punishment->duration, $punishment->description, $punishment->id);
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
        if(!$stmt) return null;
        $stmt->bind_param("si", $target, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result) return null;
        $stmt->close();
        return $result->num_rows === 1;
    }

    /**
     * @param Ban $ban
     * @return null|bool
     */
    public function saveBan(Ban $ban) : ?bool {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("INSERT INTO bans(target, moderator, expiry_time, pun_id, creation_time) VALUES(?, ?, ?, ?, ?);");
        if(!$stmt) return false;
        $timestamp = time();
        $stmt->bind_param("ssiii", $ban->target, $ban->moderator, $ban->expiryTime, $ban->punId, $timestamp);
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
        if(!$stmt) return false;
        $stmt->bind_param("isi", $time, $target, $time);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return Ban|null
     */
    public function getBanByName(string $target) : ?Ban {
        if(!$this->checkConnection()) return null;
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=? AND expiry_time > ?;");
        if(!$stmt) return null;
        $stmt->bind_param("si", $target, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result) return null;
        $result = $result->fetch_assoc();
        if(is_null($result)) return null;
        $stmt->close();
        return new Ban($result['target'], $result['moderator'], (int) $result['expiry_time'], (int) $result['pun_id'], (int) $result['id'], (int) $result['creation_time']);
    }

    /**
     * @param string $target
     * @return Ban[]|null
     */
    public function getBanHistory(string $target) : ?array {
        if(!$this->checkConnection()) return null;
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=? ORDER BY creation_time DESC;");
        if(!$stmt) return null;
        $stmt->bind_param("s", $target);
        $stmt->execute();
        if(!$result = $stmt->get_result()) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new Ban($row['target'], $row['moderator'], (int) $row['expiry_time'], (int) $row['pun_id'], (int) $row['id'], (int) $row['creation_time']);
        }
        $stmt->close();
        return $data;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return Ban[]|null
     */
    public function getCurrentBans(int $page = 0, int $limit = 6) : ?array {
        if(!$this->checkConnection()) return null;
        $time = time();
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE expiry_time > ? ORDER BY creation_time DESC LIMIT ?, ?;");
        if(!$stmt) return null;
        $stmt->bind_param("iii", $time, $page, $limit);
        $stmt->execute();
        if(false === $result = $stmt->get_result()) return null;
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new Ban($row['target'], $row['moderator'], (int) $row['expiry_time'], (int) $row['pun_id'], (int) $row['id'], (int) $row['creation_time']);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bans WHERE expiry_time > ?;");
        if(!$stmt) return null;
        $stmt->bind_param("i", $time);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result) return null;
        $rowCount = $result->fetch_row()[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return (int) floor($sites);
    }

}
