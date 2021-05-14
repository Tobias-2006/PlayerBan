<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\lang\BaseLang;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;

abstract class BaseCommand extends PluginCommand {

    abstract function canUse(CommandSender $sender) : bool;

    public function checkPluginState(Plugin $plugin, CommandSender $sender) : bool {
        if($plugin->isDisabled()) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.plugin.disabled"));
            return false;
        }
        return true;
    }

    public function getLang() : BaseLang {
        return PlayerBan::getInstance()->getLang();
    }

    public function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

}
