<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use Exception;
use SQLite3;
use tobias14\playerban\PlayerBan;

class SqliteManager extends DataManager {

    /**
     * SqliteManager constructor.
     *
     * @param PlayerBan $plugin
     * @param string[] $settings
     */
    public function __construct(PlayerBan $plugin, array $settings) {
        $this->plugin = $plugin;
        $this->settings = $settings;
        $this->db = new SQLite3($this->plugin->getDataFolder() . 'playerban.db');
        $this->init();
    }

    /**
     * @return void
     */
    protected function init() : void {
        $this->db->query("CREATE TABLE IF NOT EXISTS bans(id INTEGER PRIMARY KEY AUTOINCREMENT, target TEXT NOT NULL, moderator TEXT NOT NULL, expiry_time INTEGER NOT NULL, pun_id INTEGER NOT NULL, creation_time INTEGER NOT NULL);");
        $this->db->query("CREATE TABLE IF NOT EXISTS punishments(id INTEGER PRIMARY KEY NOT NULL, duration INTEGER NOT NULL, description TEXT NOT NULL);");
        $this->db->query("CREATE TABLE IF NOT EXISTS logs(type INTEGER NOT NULL, description TEXT NOT NULL, moderator TEXT NOT NULL, target TEXT, creation_time INTEGER NOT NULL);");
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
     * @param int $type
     * @param string $description
     * @param string $moderator
     * @param int $creationTime
     * @param string|null $target
     * @return bool|null
     */
    public function saveLog(int $type, string $description, string $moderator, int $creationTime, string $target = null) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO logs(type, description, moderator, target, creation_time) VALUES(:type, :desc, :mod, :target, :creation);");
        $stmt->bindParam(":type", $type, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $description, SQLITE3_TEXT);
        $stmt->bindParam(":mod", $moderator, SQLITE3_TEXT);
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":creation", $creationTime, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array[]|null
     */
    public function getLogs(int $page = 0, int $limit = 6) : ?array {
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM logs ORDER BY creation_time DESC LIMIT :x, :y;");
        $stmt->bindParam(":x", $page, SQLITE3_INTEGER);
        $stmt->bindParam(":y", $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(false == $result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM logs;");
        $result = $stmt->execute();
        if(false == $result) return null;
        $rowCount = $result->fetchArray(SQLITE3_NUM)[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return (int) floor($sites);
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function punishmentExists(int $id) : ?bool {
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id=:id;");
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $numRows = 0;
        while($result->fetchArray()){
            ++$numRows;
        }
        $stmt->close();
        return $numRows === 1;
    }

    /**
     * @param int $id
     * @return string[]|int[]|null
     */
    public function getPunishment(int $id) : ?array {
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id = :id;");
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * @return array[]|null
     */
    public function getAllPunishments() : ?array {
        $result = $this->db->query("SELECT * FROM punishments;");
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return bool|null
     */
    public function savePunishment(int $id, int $duration, string $description) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO punishments(id, duration, description) VALUES(:id, :duration, :desc);");
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $stmt->bindParam(":duration", $duration, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $description, SQLITE3_TEXT);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function deletePunishment(int $id) : ?bool {
        $stmt = $this->db->prepare("DELETE FROM punishments WHERE id=:id;");
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return bool|null
     */
    public function updatePunishment(int $id, int $duration, string $description) : ?bool {
        $stmt = $this->db->prepare("UPDATE punishments SET duration=:duration, description=:desc WHERE id=:id;");
        $stmt->bindParam(":duration", $duration, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $description, SQLITE3_TEXT);
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return bool|null
     */
    public function isBanned(string $target) : ?bool {
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=:target AND expiry_time > :time;");
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $numRows = 0;
        while($result->fetchArray()){
            ++$numRows;
        }
        $stmt->close();
        return $numRows === 1;
    }

    /**
     * @param string $target
     * @param string $moderator
     * @param int $expiryTime
     * @param int $punId
     * @param int $creationTime
     * @return bool|null
     */
    public function saveBan(string $target, string $moderator, int $expiryTime, int $punId, int $creationTime) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO bans(target, moderator, expiry_time, pun_id, creation_time) VALUES(:target, :mod, :expiry, :pun_id, :creation);");
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":mod", $moderator, SQLITE3_TEXT);
        $stmt->bindParam(":expiry", $expiryTime, SQLITE3_INTEGER);
        $stmt->bindParam(":pun_id", $punId, SQLITE3_INTEGER);
        $stmt->bindParam(":creation", $creationTime, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return bool|null
     */
    public function removeBan(string $target) : ?bool {
        $time = time();
        $stmt = $this->db->prepare("UPDATE bans SET expiry_time=:time WHERE target=:target AND expiry_time > :time;");
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return string[]|int[]|null
     */
    public function getBanByName(string $target) : ?array {
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=:target AND expiry_time > :time;");
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return array[]|null
     */
    public function getBanHistory(string $target) : ?array {
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=:target ORDER BY creation_time DESC;");
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array[]|null
     */
    public function getCurrentBans(int $page = 0, int $limit = 6) : ?array {
        $time = time();
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE expiry_time > :time ORDER BY creation_time DESC LIMIT :site, :limit;");
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $stmt->bindParam(":site", $page, SQLITE3_INTEGER);
        $stmt->bindParam(":limit", $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(false == $result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
        $time = time();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bans WHERE expiry_time > :time;");
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $rowCount = $result->fetchArray(SQLITE3_NUM)[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return (int) floor($sites);
    }

}
