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
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission());
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool{
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->translate("permission.denied"));
            return true;
        }
        $data = $this->getDataMgr()->getAllPunishments();
        if(is_null($data)) {
            $sender->sendMessage(C::RED . $this->translate("error"));
            return true;
        }
        $sender->sendMessage($this->translate("punlist.headline"));
        foreach ($data as $row) {
            $sender->sendMessage($this->translate(
                "punlist.format",
                [$row['id'], $row['description'], Converter::secondsToStr((int) $row['duration'])]
            ));
        }
        return true;
    }

}
