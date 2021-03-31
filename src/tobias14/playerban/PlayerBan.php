<?php

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use tobias14\playerban\database\Database;

class PlayerBan extends PluginBase {

    private static $instance;
    private $baseLang;

    public static function getInstance() : self {
        return self::$instance;
    }

    public function getLang() : BaseLang {
        return $this->baseLang;
    }

    public function onLoad() {
        self::$instance = $this;

        $this->saveDefaultConfig();

        $this->saveResource("eng.ini");
        $this->baseLang = new BaseLang("eng", $this->getDataFolder());

        if(false === Database::checkConnection()) {
            $this->getLogger()->error($this->getLang()->translateString("db.connection.failed"));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        Database::init();
    }

    public function onEnable() {
        $command_map = $this->getServer()->getCommandMap();
        $commands = ["ban", "unban", "pardon", "ban-ip", "unban-ip"];
        foreach ($commands as $cmd) {
            if(!is_null($command = $command_map->getCommand($cmd))) {
                $command_map->unregister($command);
            }
        }
    }

}
