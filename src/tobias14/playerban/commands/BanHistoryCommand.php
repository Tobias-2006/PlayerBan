<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanHistoryForm;
use tobias14\playerban\PlayerBan;

class BanHistoryCommand extends BaseCommand {

    /**
     * BanHistoryCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("banhistory.name"), $plugin);
        $this->setPermission($this->translate("banhistory.permission"));
        $this->setDescription($this->translate("banhistory.description"));
        $this->setUsage($this->translate("banhistory.usage"));
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
        $bans = $this->getDataMgr()->getBanHistory($target);
        if(is_null($bans)) {
            $sender->sendMessage($this->translate(C::RED . "error"));
            return true;
        }
        if(count($bans) === 0) {
            $sender->sendMessage(C::RED . $this->translate("target.neverBanned", [$target]));
            return true;
        }
        if($sender instanceof ConsoleCommandSender) {
            foreach ($bans as $ban) {
                $banCreation = PlayerBan::getInstance()->formatTime($ban->creationTime);
                $punishment = $this->getDataMgr()->getPunishment($ban->punId) ?? ['description' => 'undefined'];
                $sender->sendMessage($this->translate("banhistory.consoleFormat", [$banCreation, $punishment['description']]));
            }
            return true;
        }
        if(!$sender instanceof Player)
            return true;
        $sender->sendForm(new BanHistoryForm($target));
        return true;
    }

}
