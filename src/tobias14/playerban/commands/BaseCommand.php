<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\BanManager;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\PunishmentManager;

abstract class BaseCommand extends PluginCommand {

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
     * Punishment Management
     *
     * @return PunishmentManager
     */
    public function getPunishmentMgr() : PunishmentManager {
        return PlayerBan::getInstance()->getPunishmentManager();
    }

    /**
     * Ban Management
     *
     * @return BanManager
     */
    public function getBanMgr() : BanManager {
        return PlayerBan::getInstance()->getBanManager();
    }

}
