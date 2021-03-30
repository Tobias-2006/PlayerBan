<?php

namespace tobias14\playerban;

use pocketmine\plugin\PluginBase;
use tobias14\playerban\database\Database;

class PlayerBan extends PluginBase {

    private static $instance;

    public static function getInstance() : self {
        return self::$instance;
    }

    public function onEnable() {
        self::$instance = $this;

        $this->saveDefaultConfig();

        if(false === Database::checkConnection()) {
            $this->getLogger()->error("Could not connect to database!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        Database::init();

        $command_map = $this->getServer()->getCommandMap();
        $commands = ["ban", "unban", "pardon", "ban-ip", "unban-ip"];
        foreach ($commands as $cmd) {
            if(!is_null($command = $command_map->getCommand($cmd))) {
                $command_map->unregister($command);
            }
        }
    }

}
