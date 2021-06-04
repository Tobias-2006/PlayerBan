<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\forms\subforms\BanSubForm;
use tobias14\playerban\PlayerBan;

class BanForm extends SimpleBaseForm {

    /**
     * BanForm constructor.
     */
    public function __construct() {
        $players = PlayerBan::getInstance()->getServer()->getOnlinePlayers();
        parent::__construct($this->onCall($players));
        $this->setTitle($this->translate("ban.form.title"));
        foreach ($players as $player)
            $this->addButton($this->translate("ban.form.button", [$player->getName()]));
    }

    /**
     * @param Player[] $players
     * @return callable
     */
    public function onCall(array $players) : callable {
        return function (Player $player, $data) use ($players) {
            if(is_null($data)) return;
            $player->sendForm(new BanSubForm($players[array_keys($players)[$data]]));
        };
    }

}
