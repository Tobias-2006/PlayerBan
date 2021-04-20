<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\utils\Converter;

class PunishmentListCommand extends PluginCommand {

    /**
     * PunishmentListCommand constructor.
     *
     * @param PlayerBan $owner
     */
    public function __construct(PlayerBan $owner) {
        parent::__construct("punishmentlist", $owner);
        $this->setAliases(["punlist"]);
        $this->setPermission("playerban.command.punishmentlist");
        $this->setDescription("Create or edit punishments");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool{
        if($this->getPlugin()->isDisabled()) {
            $sender->sendMessage(C::RED . $this->getPlugin()->getLang()->translateString("command.plugin.disabled"));
            return true;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.permission.denied"));
            return true;
        }
        $data = $this->getPlugin()->getDataManager()->getAllPunishments();
        if(is_null($data)) {
            $sender->sendMessage(C::RED . $this->getPlugin()->getLang()->translateString("command.error"));
            return true;
        }
        $sender->sendMessage($this->getPlugin()->getLang()->translateString("command.punlist.title"));
        foreach ($data as $row) {
            $msg = $this->getPlugin()->getLang()->translateString("command.punlist.output", [$row['id'], $row['description'], Converter::seconds_to_str($row['duration'])]);
            $sender->sendMessage($msg);
        }
        return true;
    }

}
