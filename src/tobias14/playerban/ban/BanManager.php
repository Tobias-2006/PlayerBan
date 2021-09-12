<?php
declare(strict_types=1);

namespace tobias14\playerban\ban;

use tobias14\playerban\events\{PlayerBanTargetBanEvent, PlayerBanTargetUnbanEvent};
use tobias14\playerban\PlayerBan;

class BanManager {

    /** @var PlayerBan $plugin */
    protected $plugin;

    /**
     * BanManager constructor.
     *
     * @param PlayerBan $plugin
     */
    public function __construct(PlayerBan $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Ban a player/ip
     *
     * @param Ban $ban
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function add(Ban $ban, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->isBanned($ban->target, function(bool $banned) use ($ban, $onSuccess, $onFailure) {
            if($banned) {
                if(!is_null($onFailure)) {
                    $onFailure();
                    return;
                }
                throw new InvalidTargetException('Tried to ban a target that is already banned!');
            }
            $event = new PlayerBanTargetBanEvent($ban);
            $event->call();
            if($event->isCancelled())
                return;
            $ban = $event->getBan();
            if(!$this->plugin->isValidUserName($ban->target) and !$this->plugin->isValidAddress($ban->target))
                throw new InvalidTargetException('');
            $this->plugin->getDataManager()->saveBan($ban, function(int $insertId, int $affectedRows) use ($onSuccess) {
                if(!is_null($onSuccess))
                    $onSuccess($insertId, $affectedRows);
            }, $onFailure);
        });
    }

    /**
     * Unban a player/ip
     *
     * @param string $target
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function remove(string $target, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->get($target, function(Ban $ban) use ($onSuccess, $onFailure) {
            $event = new PlayerBanTargetUnbanEvent($ban);
            $event->call();
            if($event->isCancelled())
                return;
            $this->plugin->getDataManager()->removeBan(
                $event->getBan()->target,
                $onSuccess,
                $onFailure
            );
        });
    }

    /**
     * Check if a player/ip is banned
     *
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function isBanned(string $target, callable $onSuccess, callable $onFailure = null) : void {
        $this->plugin->getDataManager()->getBanByName($target, function (array $rows) use ($onSuccess) {
            $onSuccess(count($rows) > 0);
        }, $onFailure);
    }

    /**
     * Gets a ban instance if the player/ip is banned
     *
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function get(string $target, callable $onSuccess, callable $onFailure = null) : void {
        $this->isBanned($target, function (bool $banned) use ($target, $onSuccess, $onFailure) {
            if(!$banned) {
                if(!is_null($onFailure))
                    $onFailure();
                return;
            }
            $this->plugin->getDataManager()->getBanByName($target, function (array $rows) use ($onSuccess) {
                $row = $rows[0];
                $ban = new Ban($row['target'], $row['moderator'], (int) $row['expiry_time'], (int) $row['pun_id'], (int) $row['id'], (int) $row['creation_time']);
                $onSuccess($ban);
            }, $onFailure);
        });
    }

    /**
     * Gets a list of all bans of a player/ip
     *
     * @param string $target
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getHistory(string $target, callable $onSuccess, callable $onFailure = null) : void {
        $this->plugin->getDataManager()->getBanHistory($target, function (array $rows) use ($onSuccess) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = new Ban($row['target'], $row['moderator'], (int) $row['expiry_time'], (int) $row['pun_id'], (int) $row['id'], (int) $row['creation_time']);
            }
            $onSuccess($data);
        }, $onFailure);
    }

}
