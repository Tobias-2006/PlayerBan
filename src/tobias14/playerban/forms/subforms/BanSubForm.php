<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use tobias14\playerban\forms\SimpleBaseForm;
use tobias14\playerban\punishment\Punishment;

class BanSubForm extends SimpleBaseForm {

    /**
     * BanSubForm constructor.
     *
     * @param Player $player
     * @param Punishment[] $punishments
     */
    public function __construct(Player $player, array $punishments) {
        parent::__construct($this->onCall($player, $punishments));
        $this->setTitle($this->translate("ban.form2.title"));
        foreach ($punishments as $punishment)
            $this->addButton($this->translate("ban.form2.button", [$punishment->description]));
    }

    /**
     * @param Player $target
     * @param Punishment[] $punishments
     * @return callable
     */
    public function onCall(Player $target, array $punishments) : callable {
        return function (Player $player, $data) use ($target, $punishments) {
            if(is_null($data)) return;
            $player->sendForm(new BanSubForm2($target, $punishments[$data]));
        };
    }

}
