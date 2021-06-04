<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\forms\subforms\BanLogsSubForm;
use tobias14\playerban\log\Log;

class BanLogsForm extends SimpleBaseForm {

    /**
     * BanLogsForm constructor.
     *
     * @param int $page
     */
    public function __construct(int $page = 0) {
        $logs = $this->getDataMgr()->getLogs($page) ?? [];
        parent::__construct($this->onCall($logs, $page));
        $this->setTitle($this->translate("banlogs.form.title"));
        foreach ($logs as $log)
            $this->addButton($this->translate("banlogs.form.button", [$this->formatTime($log->creationTime), $log->moderator]));
        if($this->getDataMgr()->getMaxLogPage() > ($page + 1))
            $this->addButton($this->translate("button.nextPage"));
    }

    /**
     * @param Log[] $logs
     * @param int $page
     * @return callable
     */
    protected function onCall(array $logs, int $page) : callable {
        return function (Player $player, $data) use ($logs, $page) {
            if(is_null($data)) return;
            if($data === (count($logs))) {
                $player->sendForm(new BanLogsForm($page + 1));
                return;
            }
            $player->sendForm(new BanLogsSubForm($logs[$data], $page));
        };
    }

}
