<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use poggit\libasynql\SqlError;
use tobias14\playerban\forms\ModalBaseForm;
use tobias14\playerban\log\{Log, Logger};
use tobias14\playerban\PlayerBan;

class BanLogsSubForm2 extends ModalBaseForm {

    /**
     * BanLogsSubForm2 constructor.
     *
     * @param Log $log
     */
    public function __construct(Log $log) {
        parent::__construct($this->onCall($log));
        $this->setTitle($this->translate("banlogs.form3.title"));
        $this->setContent($this->translate("banlogs.form3.content"));
        $this->setButton1($this->translate("button.confirm"));
        $this->setButton2($this->translate("button.cancel"));
    }

    /**
     * @param Log $log
     * @return callable
     */
    public function onCall(Log $log) : callable {
        return function(Player $player, $data) use ($log) {
            if(!$data)
                return;
            Logger::getLogger()->delete($log, function () use ($player) {
                $player->sendMessage($this->translate("banlogs.deleteLog.success"));
            }, function (SqlError $error) use ($player) {
                $player->sendMessage($this->translate("error"));
                PlayerBan::getInstance()->getLogger()->error($error->getMessage());
            });
        };
    }

}
