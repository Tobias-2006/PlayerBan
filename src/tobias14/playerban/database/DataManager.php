<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use tobias14\playerban\ban\Ban;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

abstract class DataManager {

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
     * Returns a punishment or null
     *
     * @param int $id
     * @return Punishment|null
     */
    abstract public function getPunishment(int $id) : ?Punishment;

    /**
     * Returns a list of all punishments
     *
     * @return Punishment[]|null
     */
    abstract public function getAllPunishments() : ?array;

    /**
     * Saves a punishment to the database
     *
     * @param Punishment $punishment
     * @return bool|null
     */
    abstract public function savePunishment(Punishment $punishment) : ?bool;

    /**
     * Deletes a punishment from the database
     *
     * @param Punishment $punishment
     * @return bool|null
     */
    abstract public function deletePunishment(Punishment $punishment) : ?bool;

    /**
     * Allows to edit an existing punishment
     *
     * @param Punishment $punishment
     * @return bool|null
     */
    abstract public function updatePunishment(Punishment $punishment) : ?bool;

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
     * @param Ban $ban
     * @return bool|null
     */
    abstract public function saveBan(Ban $ban) : ?bool;

    /**
     * Resets the ban duration
     *
     * @param string $target
     * @return bool|null
     */
    abstract public function removeBan(string $target) : ?bool;

    /**
     * Returns a ban instance
     *
     * @param string $target
     * @return Ban|null
     */
    abstract public function getBanByName(string $target) : ?Ban;

    /**
     * Returns a list of all bans of a player or an ip address
     *
     * @param string $target
     * @return Ban[]|null
     */
    abstract public function getBanHistory(string $target) : ?array;

    /**
     * Returns a list of all active bans for the requested page
     *
     * @param int $page
     * @param int $limit
     * @return Ban[]|null
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
