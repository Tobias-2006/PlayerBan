<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;

class UnbanCommand extends BaseCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("unban", $owner);
        $this->setPermission("playerban.command.unban");
        $this->setDescription("Unban someone from the server");
        $this->setUsage("/unban <player|ip>");
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission());
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.permission.denied"));
            return true;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return true;
        }
        $target = $args[0];
        if(!$this->getDataMgr()->isBanned($target)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.target.isNotBanned", [$target]));
            return true;
        }

        if($this->getDataMgr()->removeBan($target)) {
            $sender->sendMessage($this->getLang()->translateString("command.unban.success", [$target]));
            return true;
        }

        $sender->sendMessage(C::RED . $this->getLang()->translateString("command.error"));
        return true;
    }

}