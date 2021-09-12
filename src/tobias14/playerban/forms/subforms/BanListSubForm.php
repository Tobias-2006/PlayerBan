<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\{BanListForm, SimpleBaseForm};
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

class BanListSubForm extends SimpleBaseForm {

    /**
     * BanListSubForm constructor.
     *
     * @param Ban $ban
     * @param int $page
     * @param Punishment|null $punishment
     */
    public function __construct(Ban $ban, int $page, ?Punishment $punishment = null) {
        parent::__construct($this->onCall($ban, $page));
        $this->setTitle($this->translate("banlist.form2.title"));
        $this->setContent($this->getFormContent($ban, $punishment));
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
            BanListForm::getCurrentBansForPage(function(array $bans) use ($player, $page) {
                BanListForm::getMaxCurrentBansPage(function($maxPage) use ($player, $page, $bans) {
                    $player->sendForm(new BanListForm($bans, $maxPage, $page));
                });
            }, null, $page);
        };
    }

    /**
     * Returns a string, with the information lines
     *
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
            $line = $this->translate("banlist.form2.line$line", [$params[$i]]);
            if($i === 0)
                $line .= "\n";
            $data[] = $line;
        }
        return implode("\n", $data);
    }

}
