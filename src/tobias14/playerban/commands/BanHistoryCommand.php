<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\{CommandSender, ConsoleCommandSender};
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
        if(($player = $this->getPlugin()->getServer()->getPlayer($target)) !== null)
            $target = $player->getName();

        $this->getBanMgr()->getHistory($target, function(array $bans) use ($sender, $target) {
            if(count($bans) === 0) {
                $sender->sendMessage(C::RED . $this->translate("target.neverBanned", [$target]));
                return;
            }
            if($sender instanceof ConsoleCommandSender) {
                foreach ($bans as $ban) {
                    $this->getPunishmentMgr()->get($ban->punId, function(Punishment $punishment) use ($sender, $ban) {
                        $banCreation = PlayerBan::getInstance()->formatTime($ban->creationTime);
                        $sender->sendMessage($this->translate("banhistory.consoleFormat", [$banCreation, $punishment->description]));
                    });
                }
                return;
            }
            if(!$sender instanceof Player)
                return;
            $sender->sendForm(new BanHistoryForm($target, $bans));
        }, function() use ($sender) {
            $sender->sendMessage(C::RED . $this->translate("error"));
        });
        return true;
    }

}
