<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanListForm;
use tobias14\playerban\forms\SimpleBaseForm;
use tobias14\playerban\utils\Converter;

class BanListSubForm extends SimpleBaseForm {

    /**
     * BanListSubForm constructor.
     *
     * @param string[]|int[] $ban
     * @param int $page
     */
    public function __construct(array $ban, int $page) {
        parent::__construct($this->onCall($ban, $page));
        $this->setTitle($this->translate("banlist.form2.title"));
        $this->setContent($this->getFormContent($ban));
        $this->addButton(C::RED . $this->translate("banlist.form2.button"));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param string[]|int[] $ban
     * @param int $page
     * @return callable
     */
    protected function onCall(array $ban, int $page) : callable {
        return function (Player $player, $data) use($ban, $page) {
            if(is_null($data)) return;
            if($data === 0) {
                $player->getServer()->dispatchCommand($player, 'unban "' . $ban["target"] . '"');
                return;
            }
            $player->sendForm(new BanListForm($page));
        };
    }

    /**
     * Returns a string, with the information lines
     *
     * @param string[]|int[] $ban
     * @return string
     */
    private function getFormContent(array $ban) : string {
        $data = [];
        $params = [$ban['id'], $this->formatTime($ban['creation_time']), $ban['target'], $ban['moderator'], $this->formatTime($ban['expiry_time']), $ban['pun_id']];
        for ($i = 0; $i < 8; $i++) {
            $line = $i + 1;
            if($i === 6) {
                if($this->getDataMgr()->punishmentExists($ban['pun_id'])) {
                    $punishment = $this->getDataMgr()->getPunishment($ban['pun_id']);
                    $params[] = $punishment['description'];
                    $params[] = Converter::secondsToStr($punishment['duration']);
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
