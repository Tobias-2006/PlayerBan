<?php

namespace tobias14\playerban\database;

use tobias14\playerban\PlayerBan;

/**
 * Class DataManager
 * @package tobias14\playerban\database
 */
abstract class DataManager {

    protected $db;
    /** @var PlayerBan $plugin */
    protected $plugin;
    /** @var array $settings */
    protected $settings;

    abstract public function __construct(PlayerBan $plugin, array $settings);

    abstract protected function init();
    abstract public function close();

    abstract public function saveLog(int $type, string $description, string $moderator, int $creationTime, string $target = null) : ?bool;
    abstract public function getLogs(int $site = 0, int $limit = 6) : ?array;
    abstract public function getMaxLogPage(int $limit = 6) : ?int;

    abstract public function punishmentExists(int $id) : ?bool;
    abstract public function getPunishment(int $id) : ?array;
    abstract public function getAllPunishments() : ?array;
    abstract public function savePunishment(int $id, int $duration, string $description) : ?bool;
    abstract public function deletePunishment(int $id) : ?bool;
    abstract public function updatePunishment(int $id, int $duration, string $description) : ?bool;

    abstract public function isBanned(string $target) : ?bool;
    abstract public function saveBan(string $target, string $moderator, int $expiryTime, int $punId, int $creationTime) : ?bool;
    abstract public function removeBan(string $target) : ?bool;
    abstract public function getBanByName(string $target) : ?array;
    abstract public function getAllCurrentBans(int $site = 0, int $limit = 6) : ?array;
    abstract public function getMaxBanPage(int $limit = 6) : ?int;

}
