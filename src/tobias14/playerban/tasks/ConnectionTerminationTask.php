<?php

namespace tobias14\playerban\tasks;

use pocketmine\scheduler\Task;
use tobias14\playerban\PlayerBan;

/**
 * This class terminates the connection to the database if it is not used for 60 seconds
 *
 * Class ConnectionTerminationTask
 * @package tobias14\playerban\tasks
 */
class ConnectionTerminationTask extends Task {

    /** @var self $instance */
    private static $instance;

    /** @var PlayerBan $plugin */
    public $plugin;
    /** @var int $timer */
    public $timer;

    /**
     * ConnectionTerminationTask constructor.
     *
     * @param PlayerBan $plugin
     */
    public function __construct(PlayerBan $plugin) {
        self::$instance = $this;
        $this->plugin = $plugin;
        $this->timer = 60;
    }

    /**
     * @return self|null
     */
    public static function getInstance() : ?self {
        return self::$instance;
    }

    public function onRun(int $currentTick) {
        $this->timer--;
        if($this->timer === 0) {
            $this->plugin->getDataManager()->close();
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
            self::$instance = null;
        }
    }

}
