<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\Ban;

/**
 * Class BanCommand
 * @package tobias14\playerban\commands
 */
class BanCommand extends BaseCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("ban", $owner);
        $this->setPermission("playerban.command.ban");
        $this->setDescription("Ban a player from the server");
        $this->setUsage("/ban <player|ip> <punId>");
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
        if(!isset($args[0]) or !isset($args[1])) {
            $sender->sendMessage($this->getUsage());
            return true;
        }
        $target = &$args[0];
        $pun_id = &$args[1];
        if(strlen($target) < 4) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.param.tooShort", ["<player|ip>", "4"]));
            return true;
        }
        if($this->getDataMgr()->isBanned($target)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.target.alreadyBanned"));
            return true;
        }
        if(!is_numeric($pun_id)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.param.incorrectFormat", ["<punId>", "3"]));
            return true;
        }
        if(!$this->getDataMgr()->punishmentExists($pun_id)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.punishment.doesNotExist", [$pun_id]));
            return true;
        }
        $punishment = $this->getDataMgr()->getPunishment($pun_id);

        $ban = new Ban();
        $ban->target = $target;
        $ban->moderator = $sender->getName();
        $ban->expiry_time = time() + $punishment['duration'];
        $ban->pun_id = $pun_id;

        if($ban->save()) {
            $sender->sendMessage($this->getLang()->translateString("command.ban.success", [$target]));
            return true;
        }

        $sender->sendMessage(C::RED . $this->getLang()->translateString("command.error"));
        return true;
    }

}
