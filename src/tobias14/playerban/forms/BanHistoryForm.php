<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\subforms\BanHistorySubForm;

class BanHistoryForm extends SimpleBaseForm {

    /**
     * BanHistoryForm constructor.
     *
     * @param string $target
     */
    public function __construct(string $target) {
        $bans = $this->getDataMgr()->getBanHistory($target) ?? [];
        parent::__construct($this->onCall($target, $bans));
        $this->setTitle($this->translate("banhistory.form.title", [$target]));
        foreach ($bans as $ban) {
            $creation = $this->formatTime($ban->creationTime);
            $this->addButton($this->translate("banhistory.form.button", [$creation]));
        }
    }

    /**
     * @param string $target
     * @param Ban[] $bans
     * @return callable
     */
    public function onCall(string $target, array $bans) : callable {
        return function (Player $player, $data) use ($target, $bans) {
            if(is_null($data)) return;
            $player->sendForm(new BanHistorySubForm($target, $bans[$data]));
        };
    }

}
