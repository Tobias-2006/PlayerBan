<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanListForm;

/**
 * Class BanListCommand
 *
 * @package tobias14\playerban\commands
 */
class BanListCommand extends BaseCommand {

    /**
     * BanListCommand constructor.
     *
     * @param Plugin $owner
     */
    public function __construct(Plugin $owner) {
        parent::__construct("banlist", $owner);
        $this->setPermission("playerban.command.banlist");
        $this->setDescription("Shows a list of all banned players");
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission()) and $sender instanceof Player;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.permission.denied"));
            return true;
        }
        /** @var Player $player */
        $player = &$sender;
        BanListForm::openMainForm($player);
        return true;
    }

}
