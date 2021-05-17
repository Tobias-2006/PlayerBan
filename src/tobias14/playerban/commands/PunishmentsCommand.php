<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\PunishmentForm;

/**
 * Class PunishmentsCommand
 *
 * @package tobias14\playerban\commands
 */
class PunishmentsCommand extends BaseCommand {

    /**
     * PunishmentsCommand constructor.
     * 
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("punishments.name"), $plugin);
        $this->setPermission($this->translate("punishments.punishments.permission"));
        $this->setDescription($this->translate("punishments.description"));
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
        PunishmentForm::openMainForm($player);
        return true;
    }

}
