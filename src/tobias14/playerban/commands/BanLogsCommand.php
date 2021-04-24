<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanLogsForm;
use tobias14\playerban\PlayerBan;

class BanLogsCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("banlogs", $owner);
        $this->setPermission("playerban.command.banlogs");
        $this->setDescription("Modification protocol");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if($this->getPlugin()->isDisabled()) {
            $sender->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.plugin.disabled"));
            return true;
        }
        if(!$sender->hasPermission($this->getPermission()) or !$sender instanceof Player) {
            $sender->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.permission.denied"));
            return true;
        }
        BanLogsForm::openMainForm($sender);
        return true;
    }

}