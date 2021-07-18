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
     * @return bool
     */
    public function add(Ban $ban) : bool {
        $event = new PlayerBanTargetBanEvent($ban);
        $event->call();
        if($event->isCancelled())
            return false;
        $ban = $event->getBan();
        if(!$this->plugin->isValidUserName($ban->target) and !$this->plugin->isValidAddress($ban->target))
            return false;
        if($this->isBanned($ban->target))
            return false;
        return $this->plugin->getDataManager()->saveBan($ban) ?? false;
    }

    /**
     * Unban a player/ip
     *
     * @param string $target
     * @return bool
     */
    public function remove(string $target) : bool {
        $ban = $this->get($target);
        if(is_null($ban))
            return false;
        $event = new PlayerBanTargetUnbanEvent($ban);
        $event->call();
        if($event->isCancelled())
            return false;
        $ban = $event->getBan();
        return $this->plugin->getDataManager()->removeBan($ban->target) ?? false;
    }

    /**
     * Check if a player/ip is banned
     *
     * @param string $target
     * @return bool|null
     */
    public function isBanned(string $target) : ?bool {
        return $this->plugin->getDataManager()->isBanned($target);
    }

    /**
     * Returns a ban instance if the player/ip is banned
     *
     * @param string $target
     * @return Ban|null
     */
    public function get(string $target) : ?Ban {
        return $this->plugin->getDataManager()->getBanByName($target);
    }

    /**
     * Returns a list of all bans of a player/ip
     *
     * @param string $target
     * @return Ban[]|null
     */
    public function getHistory(string $target) : ?array {
        return $this->plugin->getDataManager()->getBanHistory($target);
    }

}
