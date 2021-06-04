<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;


use pocketmine\Player;
use tobias14\playerban\forms\ModalBaseForm;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

class BanSubForm2 extends ModalBaseForm {

    /**
     * BanSubForm2 constructor.
     *
     * @param Player $player
     * @param Punishment $punishment
     */
    public function __construct(Player $player, Punishment $punishment) {
        parent::__construct($this->onCall($player, $punishment));
        $this->setTitle($this->translate("ban.form3.title", [$player->getName()]));
        $this->setContent($this->translate("ban.form3.content", [$player->getName(), $punishment->description]));
        $this->setButton1($this->translate("button.confirm"));
        $this->setButton2($this->translate("button.cancel"));
    }

    /**
     * @param Player $target
     * @param Punishment $punishment
     * @return callable
     */
    public function onCall(Player $target, Punishment $punishment) : callable {
        return function (Player $player, $data) use ($target, $punishment) {
            if(is_null($data)) return;
            if(!$data)
                return;
            PlayerBan::getInstance()->getServer()->dispatchCommand($player, 'ban "' . $target->getName() . '" ' . $punishment->id);
        };
    }

}
