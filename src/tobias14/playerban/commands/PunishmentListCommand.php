<?php

namespace tobias14\playerban\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\utils\Converter;

/**
 * Class PunishmentListCommand
 *
 * @package tobias14\playerban\commands
 */
class PunishmentListCommand extends BaseCommand {

    /**
     * PunishmentListCommand constructor.
     *
     * @param PlayerBan $owner
     */
    public function __construct(PlayerBan $owner) {
        parent::__construct("punlist", $owner);
        $this->setPermission("playerban.command.punlist");
        $this->setDescription("Outputs a list of all punishments");
    }

    public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission($this->getPermission());
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool{
        if(!$this->checkPluginState($this->getPlugin(), $sender))
            return true;
        if(!$this->canUse($sender)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.permission.denied"));
            return true;
        }
        $data = $this->getDataMgr()->getAllPunishments();
        if(is_null($data)) {
            $sender->sendMessage(C::RED . $this->getLang()->translateString("command.error"));
            return true;
        }
        $sender->sendMessage($this->getLang()->translateString("command.punlist.title"));
        foreach ($data as $row) {
            $sender->sendMessage($this->getLang()->translateString(
                "command.punlist.output",
                [$row['id'], $row['description'], Converter::seconds_to_str($row['duration'])]
            ));
        }
        return true;
    }

}
