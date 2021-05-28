<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\forms\subforms\BanListSubForm;

class BanListForm extends SimpleBaseForm {

    /**
     * BanListForm constructor.
     *
     * @param int $page form page
     */
    public function __construct(int $page = 0) {
        $bans = $this->getDataMgr()->getAllCurrentBans($page);
        parent::__construct($this->onCall($bans, $page));
        $this->setTitle($this->translate("banlist.form.title"));
        foreach ($bans as $ban)
            $this->addButton($this->translate("banlist.form.button", [$this->formatTime($ban['creation_time']), $ban['target']]));
        if($this->getDataMgr()->getMaxBanPage() > ($page + 1))
            $this->addButton($this->translate("button.nextPage"));
    }

    /**
     * @param array[] $bans
     * @param int $page
     * @return callable
     */
    protected function onCall(array $bans, int $page) : callable {
        return function (Player $player, $data) use ($bans, $page) {
            if(is_null($data)) return;
            if(count($bans) === $data) {
                $player->sendForm(new BanListForm($page + 1));
                return;
            }
            $player->sendForm(new BanListSubForm($bans[$data], $page));
        };
    }

}
