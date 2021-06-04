<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\BanLogsForm;
use tobias14\playerban\forms\SimpleBaseForm;
use tobias14\playerban\log\Log;
use tobias14\playerban\log\Logger;

class BanLogsSubForm extends SimpleBaseForm {

    /**
     * BanLogsSubForm constructor.
     *
     * @param Log $log
     * @param int $page
     * @param bool $isOp
     */
    public function __construct(Log $log, int $page, bool $isOp) {
        parent::__construct($this->onCall($log, $page, $isOp));
        $this->setTitle($this->translate("banlogs.form2.title"));
        $this->setContent($this->getFormContent($log));
        if($isOp)
            $this->addButton(C::RED . $this->translate("button.delete"));
        $this->addButton($this->translate("button.back"));
    }

    /**
     * @param Log $log
     * @param int $page
     * @param bool $isOp
     * @return callable
     */
    protected function onCall(Log $log, int $page, bool $isOp) : callable {
        return function (Player $player, $data) use ($log, $page, $isOp) {
            if(is_null($data)) return;
            if (0 === $data and $isOp) {
                if(Logger::getLogger()->delete($log)) {
                    $player->sendMessage("success...");
                    return;
                }
                $player->sendMessage($this->translate("error"));
                return;
            }
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
