<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\BanForm;
use tobias14\playerban\log\{Log, Logger};
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

class BanCommand extends BaseCommand {

    /**
     * BanCommand constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        parent::__construct($this->translate("ban.name"), $plugin);
        $this->setPermission($this->translate("ban.permission"));
        $this->setDescription($this->translate("ban.description"));
        $this->setUsage($this->translate("ban.usage"));
        $this->setPermissionMessage(C::RED . $this->translate("permission.denied"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->testPermission($sender))
            return true;
        if(count($args) === 0 and $sender instanceof Player) {
            $sender->sendForm(new BanForm());
            return true;
        }
        if(count($args) < 2)
            throw new InvalidCommandSyntaxException();
        $target = &$args[0];
        $punId = &$args[1];
        if(!PlayerBan::getInstance()->isValidUsername($target) && !PlayerBan::getInstance()->isValidAddress($target)) {
            $sender->sendMessage(C::RED . $this->translate("param.incorrect", ["<player|ip>", "max123"]));
            return true;
        }
        if(!is_numeric($punId)) {
            $sender->sendMessage(C::RED . $this->translate("param.incorrect", ["<punId>", "3"]));
            return true;
        }
        if(($player = $this->getPlugin()->getServer()->getPlayer($target)) !== null)
            $target = $player->getName();
        $punId = (int) round((float) $punId);

        $this->getBanMgr()->isBanned($target, function (bool $banned) use ($sender, $target, $punId) {
            if($banned) {
                $sender->sendMessage(C::RED . $this->translate("target.isBanned"));
                return;
            }
            $this->getPunishmentMgr()->get($punId, function(Punishment $punishment) use ($sender, $target, $punId) {
                $expiryTime = time() + $punishment->duration;
                $ban = new Ban($target, $sender->getName(), $expiryTime, $punId);
                $this->getBanMgr()->add($ban, function() use ($sender, $target) {
                    $sender->sendMessage($this->translate("ban.success", [$target]));
                    $log = new Log(Logger::LOG_TYPE_CREATION, $this->translate("logger.ban.creation"), $sender->getName(), $target);
                    Logger::getLogger()->log($log);
                    $this->kickTarget($target);
                }, function() use ($sender) {
                    $sender->sendMessage(C::RED . $this->translate('error'));
                });
            }, function() use ($sender) {
                $sender->sendMessage(C::RED . $this->translate('error'));
            });
        });
        return true;
    }

    /**
     * Kicks the banned player(s) from the server.
     *
     * @param string $target
     */
    private function kickTarget(string $target) : void {
        foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            if(strtolower($player->getName()) === strtolower($target) or $player->getAddress() === $target) {
                $player->kick($this->translate("ban.target.kick"), false);
            }
        }
    }

}
