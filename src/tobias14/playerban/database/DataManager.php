<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use tobias14\playerban\ban\Ban;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

abstract class DataManager {

    /** @var PlayerBan $plugin */
    protected $plugin;

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
     * Closes the DataConnector
     *
     * @return void
     */
    abstract public function close() : void;

    /**
     * Disables async mode for existing queries
     *
     * @return void
     */
    abstract public function block() : void;

    /**
     * Saves a log into the database
     *
     * @param Log $log
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function saveLog(Log $log, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Deletes a log
     *
     * @param Log $log
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function deleteLog(Log $log, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Gets a list of logs for the requested page
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     * @return void
     */
    abstract public function getLogsForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void;

    /**
     * Gets the number of log pages
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit Maximum number of logs per page
     * @return void
     */
    abstract public function getLogCount(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void;

    /**
     * Gets a punishment by id
     *
     * @param int $id
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function getPunishment(int $id, callable $onSuccess, callable $onFailure = null) : void;

    /**
     * Gets a list of all punishments
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function getAllPunishments(callable $onSuccess, callable $onFailure = null) : void;

    /**
     * Saves a punishment to the database
     *
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function savePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Deletes a punishment from the database
     *
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function deletePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Allows to overwrite an existing punishment
     *
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function updatePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Saves a ban to the database
     *
     * @param Ban $ban
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function saveBan(Ban $ban, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Resets the ban expiration
     *
     * @param string $target
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function removeBan(string $target, callable $onSuccess = null, callable $onFailure = null) : void;

    /**
     * Gets a ban by name/ip
     *
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function getBanByName(string $target, callable $onSuccess, callable $onFailure = null) : void;

    /**
     * Gets a list of all bans of a player or an ip address
     *
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    abstract public function getBanHistory(string $target, callable $onSuccess, callable $onFailure = null) : void;

    /**
     * Gets a list of all active bans for the requested page
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     * @return void
     */
    abstract public function getCurrentBansForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void;

    /**
     * Gets the number of banlist pages
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit Maximum number of bans per page
     * @return void
     */
    abstract public function getCurrentBansCount(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void;

}
