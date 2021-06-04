<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\BanHistoryForm;
use tobias14\playerban\forms\SimpleBaseForm;
use tobias14\playerban\utils\Converter;

class BanHistorySubForm extends SimpleBaseForm {

    /**
     * BanHistorySubForm constructor.
     *
     * @param string $target
     * @param Ban $ban
     */
    public function __construct(string $target, Ban $ban) {
        parent::__construct($this->onCall($target));
        $this->setTitle($this->translate("banhistory.form2.title"));
        $this->setContent($this->getFormContent($ban));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param string $target
     * @return callable
     */
    public function onCall(string $target) : callable {
        return function (Player $player, $data) use ($target) {
            if(is_null($data)) return;
            $player->sendForm(new BanHistoryForm($target));
        };
    }

    /**
     * @param Ban $ban
     * @return string
     */
    private function getFormContent(Ban $ban) : string {
        $data = [];
        $params = [$ban->id, $this->formatTime($ban->creationTime), $ban->target, $ban->moderator, $this->formatTime($ban->expiryTime), $ban->punId];
        for ($i = 0; $i < 8; $i++) {
            $line = $i + 1;
            if($i === 6) {
                if($this->getPunishmentMgr()->exists($ban->punId)) {
                    $punishment = $this->getPunishmentMgr()->get($ban->punId);
                    $params[] = $punishment->description;
                    $params[] = Converter::secondsToStr($punishment->duration);
                } else{
                    break;
                }
            }
            $line = $this->translate("banhistory.form2.line$line", [$params[$i]]);
            if($i === 0)
                $line .= "\n";
            $data[] = $line;
        }
        return implode("\n", $data);
    }
}
