<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\{BanHistoryForm, SimpleBaseForm};
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

class BanHistorySubForm extends SimpleBaseForm {

    /**
     * BanHistorySubForm constructor.
     *
     * @param string $target
     * @param Ban $ban
     * @param Ban[] $bans
     * @param Punishment|null $punishment
     */
    public function __construct(string $target, Ban $ban, array $bans, ?Punishment $punishment = null) {
        parent::__construct($this->onCall($target, $bans));
        $this->setTitle($this->translate("banhistory.form2.title"));
        $this->setContent($this->getFormContent($ban, $punishment));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param string $target
     * @param Ban[] $bans
     * @return callable
     */
    public function onCall(string $target, array $bans) : callable {
        return function (Player $player, $data) use ($target, $bans) {
            if(is_null($data)) return;
            $player->sendForm(new BanHistoryForm($target, $bans));
        };
    }

    /**
     * @param Ban $ban
     * @param Punishment|null $punishment
     * @return string
     */
    private function getFormContent(Ban $ban, ?Punishment $punishment = null) : string {
        $data = [];
        $params = [$ban->id, $this->formatTime($ban->creationTime), $ban->target, $ban->moderator, $this->formatTime($ban->expiryTime), $ban->punId];
        for ($i = 0; $i < 8; $i++) {
            $line = $i + 1;
            if($i === 6) {
                if(!is_null($punishment)) {
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
