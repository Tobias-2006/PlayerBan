<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\log\DeletionLog;
use tobias14\playerban\PlayerBan;

class UnbanCommand extends BaseCommand {

    /**
     * UnbanCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("unban.name"), $plugin);
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
        if(!PlayerBan::getInstance()->isValidUsername($target) && !PlayerBan::getInstance()->isValidAddress($target)) {
            $sender->sendMessage(C::RED . $this->translate("param.incorrect", ["<player|ip>", "max123"]));
            return true;
        }
        if(!$this->getDataMgr()->isBanned($target)) {
            $sender->sendMessage(C::RED . $this->translate("target.notBanned", [$target]));
            return true;
        }

        if($this->getDataMgr()->removeBan($target)) {
            $sender->sendMessage($this->translate("unban.success", [$target]));
            $log = new DeletionLog($this->translate("logger.ban.deletion"), $sender->getName(), $target);
            $log->save();
            return true;
        }

        $sender->sendMessage(C::RED . $this->translate("error"));
        return true;
    }

}
