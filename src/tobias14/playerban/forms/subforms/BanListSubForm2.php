<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use tobias14\playerban\forms\ModalBaseForm;

class BanListSubForm2 extends ModalBaseForm {

    /**
     * BanListSubForm2 constructor.
     *
     * @param string $target
     */
    public function __construct(string $target) {
        parent::__construct($this->onCall($target));
        $this->setTitle($this->translate("banlist.form3.title", [$target]));
        $this->setContent($this->translate("banlist.form3.content", [$target]));
        $this->setButton1($this->translate("button.confirm"));
        $this->setButton2($this->translate("button.cancel"));
    }

    /**
     * @param string $target
     * @return callable
     */
    public function onCall(string $target) : callable {
        return function (Player $player, $data) use ($target) {
            if(is_null($data)) return;
            if(!$data)
                return;
            $player->getServer()->dispatchCommand($player, 'unban "' . $target . '"');
        };
    }

}
