<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\{BanListForm, SimpleBaseForm};
use tobias14\playerban\utils\Converter;

class BanListSubForm extends SimpleBaseForm {

    /**
     * BanListSubForm constructor.
     *
     * @param Ban $ban
     * @param int $page
     */
    public function __construct(Ban $ban, int $page) {
        parent::__construct($this->onCall($ban, $page));
        $this->setTitle($this->translate("banlist.form2.title"));
        $this->setContent($this->getFormContent($ban));
        $this->addButton(C::RED . $this->translate("banlist.form2.button"));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param Ban $ban
     * @param int $page
     * @return callable
     */
    protected function onCall(Ban $ban, int $page) : callable {
        return function (Player $player, $data) use($ban, $page) {
            if(is_null($data)) return;
            if($data === 0) {
                $player->sendForm(new BanListSubForm2($ban->target));
                return;
            }
            $player->sendForm(new BanListForm($page));
        };
    }

    /**
     * Returns a string, with the information lines
     *
     * @param Ban $ban
     * @return string
     */
    private function getFormContent(Ban $ban) : string {
        $data = [];
        $params = [$ban->id, $this->formatTime($ban->creationTime), $ban->target, $ban->moderator, $this->formatTime($ban->expiryTime), $ban->punId];
        for ($i = 0; $i < 8; $i++) {
            $line = $i + 1;
            if($i === 6) {
                $punishment = $this->getPunishmentMgr()->get($ban->punId);
                if(!is_null($punishment)) {
                    $params[] = $punishment->description;
                    $params[] = Converter::secondsToStr($punishment->duration);
                } else{
                    break;
                }
            }
            $line = $this->translate("banlist.form2.line$line", [$params[$i]]);
            if($i === 0)
                $line .= "\n";
            $data[] = $line;
        }
        return implode("\n", $data);
    }

}
