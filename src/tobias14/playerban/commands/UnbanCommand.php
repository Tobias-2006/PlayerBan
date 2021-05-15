<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\log\DeletionLog;

/**
 * Class UnbanCommand
 *
 * @package tobias14\playerban\commands
 */
class UnbanCommand extends BaseCommand {

    /**
     * UnbanCommand constructor.
     *
     * @param Plugin $owner
     */
    public function __construct(Plugin $owner) {
        parent::__construct($this->translate("unban.name"), $owner);
        $this->setPermission($this->translate("unban.permission"));
        $this->setDescription($this->translate("unban.description"));
        $this->setUsage($this->translate("unban.usage"));
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission());
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->translate("permission.denied"));
            return true;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return true;
        }
        $target = $args[0];
        if(!$this->getDataMgr()->isBanned($target)) {
            $sender->sendMessage(C::RED . $this->translate("target.notBanned", [$target]));
            return true;
        }

        if($this->getDataMgr()->removeBan($target)) {
            $sender->sendMessage($this->translate("unban.success", [$target]));
            $log = new DeletionLog();
            $log->target = $target;
            $log->moderator = $sender->getName();
            $log->description = $this->translate("logger.ban.deletion");
            $log->save();
            return true;
        }

        $sender->sendMessage(C::RED . $this->translate("error"));
        return true;
    }

}
