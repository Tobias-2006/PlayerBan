<?php
declare(strict_types=1);

namespace tobias14\playerban\database;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Queries;

class MysqlManager extends DataManager implements Queries {

    /** @var DataConnector $conn */
    protected $conn;

    /**
     * DataManager constructor.
     *
     * @param PlayerBan $plugin
     * @param string[] $settings
     */
    public function __construct(PlayerBan $plugin, array $settings) {
        $this->plugin = $plugin;
        $this->conn = libasynql::create($plugin, [
            'type' => 'mysql',
            'mysql' => [
                'host' => $settings['host'],
                'username' => $settings['username'],
                'password' => $settings['passwd'],
                'schema' => $settings['dbname']
            ],
            'worker-limit' => 2
        ], [
            'mysql' => ['mysql.sql']
        ]);
        $this->init();
    }

    /**
     * @return void
     */
    protected function init() : void {
        $this->conn->executeGeneric(self::PLAYERBAN_INIT_BANS);
        $this->conn->executeGeneric(self::PLAYERBAN_INIT_PUNISHMENTS);
        $this->conn->executeGeneric(self::PLAYERBAN_INIT_LOGS);
    }

    /**
     * @return void
     */
    public function close() : void {
        if(isset($this->conn))
            $this->conn->close();
    }

    /**
     * @return void
     */
    public function block() : void {
        $this->conn->waitAll();
    }

    /**
     * @param Log $log
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function saveLog(Log $log, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeInsert(self::PLAYERBAN_LOG_SAVE, [
            'type' => $log->type,
            'description' => $log->description,
            'moderator' => $log->moderator,
            'target' => $log->target,
            'creation' => time()
        ], $onSuccess, $onFailure);
    }

    /**
     * @param Log $log
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function deleteLog(Log $log, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeChange(self::PLAYERBAN_LOG_DELETE, [
            'moderator' => $log->moderator,
            'creation' => $log->creationTime
        ], $onSuccess, $onFailure);
    }

    /**
     * The following method should be used instead
     * @link \tobias14\playerban\forms\BanLogsForm::getLogsForPage()
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     * @return void
     */
    public function getLogsForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void {
        $page *= $limit;
        $this->conn->executeSelect(self::PLAYERBAN_LOG_GET_PAGE, [
            'page' => $page,
            'limit' => $limit
        ], $onSuccess, $onFailure);
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit
     * @return void
     */
    public function getLogCount(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void {
        $this->conn->executeSelect(
            self::PLAYERBAN_LOG_GET_LOGCOUNT,
            [],
            $onSuccess,
            $onFailure
        );
    }

    /**
     * @param int $id
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getPunishment(int $id, callable $onSuccess, callable $onFailure = null) : void {
        $this->conn->executeSelect(self::PLAYERBAN_PUNISHMENT_GET, [
            'id' => $id
        ], $onSuccess, $onFailure);
    }

    /**
     * Returns a list of all punishments
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getAllPunishments(callable $onSuccess, callable $onFailure = null) : void {
        $this->conn->executeSelect(
            self::PLAYERBAN_PUNISHMENT_GET_ALL,
            [],
            $onSuccess,
            $onFailure
        );
    }

    /**
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function savePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeChange(self::PLAYERBAN_PUNISHMENT_SAVE, [
            'id' => $punishment->id,
            'duration' => $punishment->duration,
            'description' => $punishment->description
        ], $onSuccess, $onFailure);
    }

    /**
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function deletePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeChange(self::PLAYERBAN_PUNISHMENT_DELETE, [
            'id' => $punishment->id
        ], $onSuccess, $onFailure);
    }

    /**
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function updatePunishment(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeChange(self::PLAYERBAN_PUNISHMENT_UPDATE, [
            'duration' => $punishment->duration,
            'description' => $punishment->description,
            'id' => $punishment->id
        ], $onSuccess, $onFailure);
    }

    /**
     * @param Ban $ban
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function saveBan(Ban $ban, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeInsert(self::PLAYERBAN_BAN_SAVE, [
            'target' => $ban->target,
            'moderator' => $ban->moderator,
            'expiry' => $ban->expiryTime,
            'punId' => $ban->punId,
            'creation' => time()
        ], $onSuccess, $onFailure);
    }

    /**
     * @param string $target
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function removeBan(string $target, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->conn->executeChange(self::PLAYERBAN_BAN_REMOVE, [
            'timestamp' => time(),
            'target' => $target
        ], $onSuccess, $onFailure);
    }

    /**
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getBanByName(string $target, callable $onSuccess, callable $onFailure = null) : void {
        $this->conn->executeSelect(self::PLAYERBAN_BAN_GET, [
            'target' => $target,
            'timestamp' => time()
        ], $onSuccess, $onFailure);
    }

    /**
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getBanHistory(string $target, callable $onSuccess, callable $onFailure = null) : void {
        $this->conn->executeSelect(self::PLAYERBAN_BAN_GET_BANHISTORY, [
            'target' => $target
        ], $onSuccess, $onFailure);
    }

    /**
     * The following method should be used instead
     * @link \tobias14\playerban\forms\BanListForm::getCurrentBansForPage()
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     * @return void
     */
    public function getCurrentBansForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void {
        $page *= $limit;
        $this->conn->executeSelect(Queries::PLAYERBAN_BAN_GET_CURRENTBANS, [
            'timestamp' => time(),
            'page' => $page,
            'limit' => $limit
        ], $onSuccess, $onFailure);
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit
     * @return void
     */
    public function getCurrentBansCount(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void {
        $this->conn->executeSelect(Queries::PLAYERBAN_BAN_GET_CURRENTBANS_COUNT, [
            'timestamp' => time()
        ], $onSuccess, $onFailure);
    }

}
