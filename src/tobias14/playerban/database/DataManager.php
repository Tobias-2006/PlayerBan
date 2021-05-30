<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use mysqli;
use SQLite3;
use tobias14\playerban\PlayerBan;

abstract class DataManager {

    /** @var SQLite3|mysqli $db */
    protected $db;
    /** @var PlayerBan $plugin */
    protected $plugin;
    /** @var string[] $settings */
    protected $settings;

    /**
     * DataManager constructor.
     *
     * @param PlayerBan $plugin
     * @param string[] $settings
     */
    abstract public function __construct(PlayerBan $plugin, array $settings);

    /**
     * Initializes the DataManager
     *
     * @return void
     */
    abstract protected function init() : void;

    /**
     * Closes the connection to the database
     *
     * @return void
     */
    abstract public function close() : void;

    /**
     * Saves a log into the database
     *
     * @param int $type
     * @param string $description
     * @param string $moderator
     * @param int $creationTime
     * @param string|null $target
     * @return bool|null
     */
    abstract public function saveLog(int $type, string $description, string $moderator, int $creationTime, string $target = null) : ?bool;

    /**
     * Returns a list of logs for the requested page
     *
     * @param int $page
     * @param int $limit
     * @return array[]|null
     */
    abstract public function getLogs(int $page = 0, int $limit = 6) : ?array;

    /**
     * Returns the number of log pages
     *
     * @param int $limit Maximum number of logs per page
     * @return int|null
     */
    abstract public function getMaxLogPage(int $limit = 6) : ?int;

    /**
     * Checks if a punishment exists
     *
     * @param int $id
     * @return bool|null
     */
    abstract public function punishmentExists(int $id) : ?bool;

    /**
     * Returns a punishment as assoc array
     *
     * @param int $id
     * @return string[]|int[]|null
     */
    abstract public function getPunishment(int $id) : ?array;

    /**
     * Returns a list of all punishments as assoc array
     *
     * @return array[]|null
     */
    abstract public function getAllPunishments() : ?array;

    /**
     * Saves a punishment to the database
     *
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return bool|null
     */
    abstract public function savePunishment(int $id, int $duration, string $description) : ?bool;

    /**
     * Deletes a punishment from the database
     *
     * @param int $id
     * @return bool|null
     */
    abstract public function deletePunishment(int $id) : ?bool;

    /**
     * Allows to edit an existing punishment
     *
     * @param int $id
     * @param int $duration
     * @param string $description
     * @return bool|null
     */
    abstract public function updatePunishment(int $id, int $duration, string $description) : ?bool;

    /**
     * Checks if a player or an ip address is banned
     *
     * @param string $target
     * @return bool|null
     */
    abstract public function isBanned(string $target) : ?bool;

    /**
     * Saves a ban to the database
     *
     * @param string $target
     * @param string $moderator
     * @param int $expiryTime
     * @param int $punId
     * @param int $creationTime
     * @return bool|null
     */
    abstract public function saveBan(string $target, string $moderator, int $expiryTime, int $punId, int $creationTime) : ?bool;

    /**
     * Resets the ban duration
     *
     * @param string $target
     * @return bool|null
     */
    abstract public function removeBan(string $target) : ?bool;

    /**
     * Returns a ban as assoc array
     *
     * @param string $target
     * @return string[]|int[]|null
     */
    abstract public function getBanByName(string $target) : ?array;

    /**
     * Returns a list of all bans of a player or an ip address
     *
     * @param string $target
     * @return array[]|null
     */
    abstract public function getBanHistory(string $target) : ?array;

    /**
     * Returns a list of all active bans for the requested page
     *
     * @param int $page
     * @param int $limit
     * @return array[]|null
     */
    abstract public function getCurrentBans(int $page = 0, int $limit = 6) : ?array;

    /**
     * Returns the number of banlist pages
     *
     * @param int $limit Maximum number of bans per page
     * @return int|null
     */
    abstract public function getMaxBanPage(int $limit = 6) : ?int;

}
