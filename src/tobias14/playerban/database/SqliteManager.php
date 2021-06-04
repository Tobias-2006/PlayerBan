<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use Exception;
use SQLite3;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

class SqliteManager extends DataManager {

    /** @var SQLite3 $db */
    protected $db;

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
     * @param Log $log
     * @return bool|null
     */
    public function saveLog(Log $log) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO logs(type, description, moderator, target, creation_time) VALUES(:type, :desc, :mod, :target, :creation);");
        if(!$stmt) return false;
        $timestamp = time();
        $stmt->bindParam(":type", $log->type, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $log->description, SQLITE3_TEXT);
        $stmt->bindParam(":mod", $log->moderator, SQLITE3_TEXT);
        $stmt->bindParam(":target", $log->target, SQLITE3_TEXT);
        $stmt->bindParam(":creation", $timestamp, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return Log[]|null
     */
    public function getLogs(int $page = 0, int $limit = 6) : ?array {
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM logs ORDER BY creation_time DESC LIMIT :x, :y;");
        if(!$stmt) return null;
        $stmt->bindParam(":x", $page, SQLITE3_INTEGER);
        $stmt->bindParam(":y", $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(false == $result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = new Log($row['type'], $row['description'], $row['moderator'], $row['target'], $row['creation_time']);
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
        if(!$stmt) return null;
        $result = $stmt->execute();
        if(!$result) return null;
        $rowCount = $result->fetchArray(SQLITE3_NUM);
        if(!$rowCount) return null;
        $rowCount = $rowCount[0];
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
        if(!$stmt) return null;
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(!$result) return null;
        $numRows = 0;
        while($result->fetchArray()){
            ++$numRows;
        }
        $stmt->close();
        return $numRows === 1;
    }

    /**
     * @param int $id
     * @return Punishment|null
     */
    public function getPunishment(int $id) : ?Punishment {
        $stmt = $this->db->prepare("SELECT * FROM punishments WHERE id = :id;");
        if(!$stmt) return null;
        $stmt->bindParam(":id", $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(!$result) return null;
        $result = $result->fetchArray(SQLITE3_ASSOC);
        if(!$result) return null;
        $stmt->close();
        return new Punishment((int) $result['id'], (int) $result['duration'], $result['description']);
    }

    /**
     * @return Punishment[]|null
     */
    public function getAllPunishments() : ?array {
        $result = $this->db->query("SELECT * FROM punishments;");
        if(!$result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[] = new Punishment((int) $row['id'], (int) $row['duration'], $row['description']);
        }
        return $data;
    }

    /**
     * @param Punishment $punishment
     * @return bool|null
     */
    public function savePunishment(Punishment $punishment) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO punishments(id, duration, description) VALUES(:id, :duration, :desc);");
        if(!$stmt) return false;
        $stmt->bindParam(":id", $punishment->id, SQLITE3_INTEGER);
        $stmt->bindParam(":duration", $punishment->duration, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $punishment->description, SQLITE3_TEXT);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param Punishment $punishment
     * @return bool|null
     */
    public function deletePunishment(Punishment $punishment) : ?bool {
        $stmt = $this->db->prepare("DELETE FROM punishments WHERE id=:id;");
        if(!$stmt) return false;
        $stmt->bindParam(":id", $punishment->id, SQLITE3_INTEGER);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param Punishment $punishment
     * @return bool|null
     */
    public function updatePunishment(Punishment $punishment) : ?bool {
        $stmt = $this->db->prepare("UPDATE punishments SET duration=:duration, description=:desc WHERE id=:id;");
        if(!$stmt) return false;
        $stmt->bindParam(":duration", $punishment->duration, SQLITE3_INTEGER);
        $stmt->bindParam(":desc", $punishment->description, SQLITE3_TEXT);
        $stmt->bindParam(":id", $punishment->id, SQLITE3_INTEGER);
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
        if(!$stmt) return null;
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(!$result) return null;
        $numRows = 0;
        while($result->fetchArray()){
            ++$numRows;
        }
        $stmt->close();
        return $numRows === 1;
    }

    /**
     * @param Ban $ban
     * @return bool|null
     */
    public function saveBan(Ban $ban) : ?bool {
        $stmt = $this->db->prepare("INSERT INTO bans(target, moderator, expiry_time, pun_id, creation_time) VALUES(:target, :mod, :expiry, :pun_id, :creation);");
        if(!$stmt) return false;
        $timestamp = time();
        $stmt->bindParam(":target", $ban->target, SQLITE3_TEXT);
        $stmt->bindParam(":mod", $ban->moderator, SQLITE3_TEXT);
        $stmt->bindParam(":expiry", $ban->expiryTime, SQLITE3_INTEGER);
        $stmt->bindParam(":pun_id", $ban->punId, SQLITE3_INTEGER);
        $stmt->bindParam(":creation", $timestamp, SQLITE3_INTEGER);
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
        if(!$stmt) return false;
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $result = $stmt->execute() != false;
        $stmt->close();
        return $result;
    }

    /**
     * @param string $target
     * @return Ban|null
     */
    public function getBanByName(string $target) : ?Ban {
        $time = time();
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=:target AND expiry_time > :time;");
        if(!$stmt) return null;
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(!$result) return null;
        $result = $result->fetchArray(SQLITE3_ASSOC);
        if(!$result) return null;
        $stmt->close();
        return new Ban($result['target'], $result['moderator'], (int) $result['expiry_time'], (int) $result['pun_id'], (int) $result['id'], (int) $result['creation_time']);
    }

    /**
     * @param string $target
     * @return Ban[]|null
     */
    public function getBanHistory(string $target) : ?array {
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE target=:target ORDER BY creation_time DESC;");
        if(!$stmt) return null;
        $stmt->bindParam(":target", $target, SQLITE3_TEXT);
        $result = $stmt->execute();
        if(!$result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
        $time = time();
        $page *= $limit;
        $stmt = $this->db->prepare("SELECT * FROM bans WHERE expiry_time > :time ORDER BY creation_time DESC LIMIT :site, :limit;");
        if(!$stmt) return null;
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $stmt->bindParam(":site", $page, SQLITE3_INTEGER);
        $stmt->bindParam(":limit", $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(false == $result) return null;
        $data = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
        $time = time();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bans WHERE expiry_time > :time;");
        if(!$stmt) return null;
        $stmt->bindParam(":time", $time, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if(!$result) return null;
        $rowCount = $result->fetchArray(SQLITE3_NUM);
        if(!$rowCount) return null;
        $rowCount = $rowCount[0];
        $sites = $rowCount / $limit;
        if(($rowCount % $limit) != 0)
            $sites += 1;
        $stmt->close();
        return (int) floor($sites);
    }

}
