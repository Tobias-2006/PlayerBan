<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\utils\Converter;

/**
 * Class PunishmentListCommand
 *
 * @package tobias14\playerban\commands
 */
class PunishmentListCommand extends BaseCommand {

    /**
     * PunishmentListCommand constructor.
     *
     * @param PlayerBan $plugin
     */
    public function __construct(PlayerBan $plugin) {
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
                [$row['id'], $row['description'], Converter::seconds_to_str($row['duration'])]
            ));
        }
        return true;
    }

}
