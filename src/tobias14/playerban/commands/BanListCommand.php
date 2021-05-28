<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanListForm;

class BanListCommand extends BaseCommand {

    /**
     * BanListCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("banlist.name"), $plugin);
        $this->setPermission($this->translate("banlist.permission"));
        $this->setDescription($this->translate("banlist.description"));
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission()) and $sender instanceof Player;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->translate("permission.denied"));
            return true;
        }
        /** @var Player $player */
        $player = &$sender;
        $player->sendForm(new BanListForm());
        return true;
    }

}
