<?php
declare(strict_types=1);

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\log\CreationLog;
use tobias14\playerban\PlayerBan;

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
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission());
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->translate("permission.denied"));
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
        if($this->getDataMgr()->isBanned($target)) {
            $sender->sendMessage(C::RED . $this->translate("target.isBanned"));
            return true;
        }
        if(!is_numeric($punId)) {
            $sender->sendMessage(C::RED . $this->translate("param.incorrect", ["<punId>", "3"]));
            return true;
        }
        $punId = (int) round((float) $punId);
        if(!$this->getDataMgr()->punishmentExists($punId)) {
            $sender->sendMessage(C::RED . $this->translate("punishment.notExist", [$punId]));
            return true;
        }
        $punishment = $this->getDataMgr()->getPunishment($punId);

        $expiryTime = time() + (int) $punishment['duration'];
        $ban = new Ban($target, $sender->getName(), $expiryTime, $punId);

        if($this->getDataMgr()->saveBan($ban)) {
            $sender->sendMessage($this->translate("ban.success", [$target]));
            $log = new CreationLog($this->translate("logger.ban.creation"), $sender->getName(), $target);
            $log->save();
            $this->kickTarget($target);
            return true;
        }

        $sender->sendMessage(C::RED . $this->translate("error"));
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
