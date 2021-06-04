<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\utils\Converter;

class PunishmentListCommand extends BaseCommand {

    /**
     * PunishmentListCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("punlist.name"), $plugin);
        $this->setPermission($this->translate("punlist.permission"));
        $this->setDescription($this->translate("punlist.description"));
        $this->setPermissionMessage(C::RED . $this->translate("permission.denied"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool{
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->testPermission($sender))
            return true;
        $punishments = $this->getPunishmentMgr()->getAll();
        if(is_null($punishments)) {
            $sender->sendMessage(C::RED . $this->translate("error"));
            return true;
        }
        $sender->sendMessage($this->translate("punlist.headline"));
        foreach ($punishments as $punishment) {
            $sender->sendMessage($this->translate("punlist.format", [
                    $punishment->id,
                    $punishment->description,
                    Converter::secondsToStr($punishment->duration)
                ]
            ));
        }
        return true;
    }

}
