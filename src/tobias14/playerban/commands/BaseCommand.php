<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;

abstract class BaseCommand extends PluginCommand {

    /**
     * Checks if the player has the required permission for the command.
     *
     * @param CommandSender $sender
     * @return bool
     */
    abstract function canUse(CommandSender $sender) : bool;

    /**
     * Checks if the plugin is enabled/disabled.
     *
     * @param Plugin $plugin
     * @param CommandSender $sender
     * @return bool
     */
    public function checkPluginState(Plugin $plugin, CommandSender $sender) : bool {
        if($plugin->isDisabled()) {
            $sender->sendMessage(C::RED . $this->translate("plugin.disabled"));
            return false;
        }
        return true;
    }

    /**
     * Massage Management
     *
     * @param string $str
     * @param float[]|int[]|string[] $params
     * @return string
     */
    public function translate(string $str, array $params = []) : string {
        return PlayerBan::getInstance()->getLanguage()->translateString($str, $params);
    }

    /**
     * Database Management
     *
     * @return DataManager
     */
    public function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

}
