<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanLogsForm;
use tobias14\playerban\PlayerBan;

class BanLogsCommand extends BaseCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("banlogs", $owner);
        $this->setPermission("playerban.command.banlogs");
        $this->setDescription("Modification protocol");
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission()) and $sender instanceof Player;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.permission.denied"));
            return true;
        }
        /** @var Player $player */
        $player = &$sender;
        BanLogsForm::openMainForm($player);
        return true;
    }

}