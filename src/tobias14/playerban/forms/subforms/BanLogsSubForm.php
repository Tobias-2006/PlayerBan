<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use tobias14\playerban\forms\BanLogsForm;
use tobias14\playerban\forms\SimpleBaseForm;
use tobias14\playerban\log\Log;

class BanLogsSubForm extends SimpleBaseForm {

    /**
     * BanLogsSubForm constructor.
     *
     * @param Log $log
     * @param int $page
     */
    public function __construct(Log $log, int $page) {
        parent::__construct($this->onCall($page));
        $this->setTitle($this->translate("banlogs.form2.title"));
        $this->setContent($this->getFormContent($log));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param int $page
     * @return callable
     */
    protected function onCall(int $page) : callable {
        return function (Player $player, $data) use ($page) {
            if(is_null($data)) return;
            if(0 === $data)
                $player->sendForm(new BanLogsForm($page));
        };
    }

    /**
     * Returns a string, with the information lines
     *
     * @param Log $log
     * @return string
     */
    private function getFormContent(Log $log) : string {
        $data = [];
        $params = [$log->type, $log->description, $log->moderator, $log->target, $this->formatTime($log->creationTime)];
        for($i = 0; $i < 5; $i++) {
            $line = $i + 1;
            $data[] = $this->translate("banlogs.form2.line$line", [$params[$i]]);
        }
        return implode("\n", $data);
    }

}
