<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanHistoryForm;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

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
        $this->setPermissionMessage(C::RED . $this->translate("permission.denied"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->testPermission($sender))
            return true;
        if(count($args) === 0)
            throw new InvalidCommandSyntaxException();
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
                $punishment = $this->getDataMgr()->getPunishment($ban->punId) ?? new Punishment(-1, -1, "undefined");
                $sender->sendMessage($this->translate("banhistory.consoleFormat", [$banCreation, $punishment->description]));
            }
            return true;
        }
        if(!$sender instanceof Player)
            return true;
        $sender->sendForm(new BanHistoryForm($target));
        return true;
    }

}
