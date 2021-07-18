<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\forms\subforms\{PunishmentSubForm, PunishmentSubForm2};
use tobias14\playerban\punishment\Punishment;

class PunishmentForm extends SimpleBaseForm {

    /**
     * PunishmentForm constructor.
     */
    public function __construct() {
        $punishments = $this->getPunishmentMgr()->getAll() ?? [];
        parent::__construct($this->onCall($punishments));
        $this->setTitle($this->translate("punishments.form.title"));
        foreach ($punishments as $punishment)
            $this->addButton($this->translate("punishments.form.button", [$punishment->id, $punishment->description]));
        $this->addButton(self::translate("punishments.form.button2"));
    }

    /**
     * @param Punishment[] $punishments
     * @return callable
     */
    protected function onCall(array $punishments) : callable {
        return function (Player $player, $data) use ($punishments) {
            if (is_null($data)) return;
            if ($data === count($punishments)) {
                $player->sendForm(new PunishmentSubForm());
                return;
            }
            $player->sendForm(new PunishmentSubForm2($punishments[$data]));
        };
    }

}
