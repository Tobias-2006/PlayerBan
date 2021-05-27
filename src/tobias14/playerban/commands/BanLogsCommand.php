<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanLogsForm;

class BanLogsCommand extends BaseCommand {

    /**
     * BanLogsCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("banlogs.name"), $plugin);
        $this->setPermission($this->translate("banlogs.permission"));
        $this->setDescription($this->translate("banlogs.description"));
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
        BanLogsForm::openMainForm($player);
        return true;
    }

}
