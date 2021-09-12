<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\log\{Log, Logger};
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
        if(!PlayerBan::getInstance()->isValidUsername($target) && !PlayerBan::getInstance()->isValidAddress($target)) {
            $sender->sendMessage(C::RED . $this->translate("param.incorrect", ["<player|ip>", "max123"]));
            return true;
        }

        $this->getBanMgr()->isBanned($target, function (bool $banned) use ($sender, $target) {
            if(!$banned) {
                $sender->sendMessage(C::RED . $this->translate("target.notBanned", [$target]));
                return;
            }
            $this->getBanMgr()->remove($target, function() use ($sender, $target) {
                $sender->sendMessage($this->translate("unban.success", [$target]));
                $log = new Log(Logger::LOG_TYPE_DELETION, $this->translate("logger.ban.deletion"), $sender->getName(), $target);
                Logger::getLogger()->log($log);
            }, function() use ($sender) {
                $sender->sendMessage(C::RED . $this->translate('error'));
            });
        }, function() use ($sender) {
            $sender->sendMessage(C::RED . $this->translate('error'));
        });
        return true;
    }

}
