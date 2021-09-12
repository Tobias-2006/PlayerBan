<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\forms\subforms\BanLogsSubForm;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;

class BanLogsForm extends SimpleBaseForm {

    /**
     * BanLogsForm constructor.
     *
     * @param Log[] $logs
     * @param int $maxLogPage
     * @param int $page
     */
    public function __construct(array $logs, int $maxLogPage, int $page = 0) {
        parent::__construct($this->onCall($logs, $page));
        $this->setTitle($this->translate("banlogs.form.title"));
        foreach ($logs as $log)
            $this->addButton($this->translate("banlogs.form.button", [$this->formatTime($log->creationTime ?? 0), $log->moderator]));
        if($maxLogPage > ($page + 1))
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
            if($data === count($logs)) {
                self::getLogsForPage(function($logs) use ($player, $page) {
                    self::getMaxLogPage(function($maxLogPage) use ($player, $page, $logs) {
                        $player->sendForm(new BanLogsForm($logs, $maxLogPage, $page + 1));
                    });
                }, null, $page + 1);
                return;
            }
            $player->sendForm(new BanLogsSubForm($logs[$data], $page, $player->isOp()));
        };
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     */
    public static function getLogsForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void {
        PlayerBan::getInstance()->getDataManager()->getLogsForPage(function(array $rows) use ($onSuccess) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = new Log((int) $row['type'], $row['description'], $row['moderator'], $row['target'], (int) $row['creation_time']);
            }
            $onSuccess($data);
        }, $onFailure, $page, $limit);
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit
     */
    public static function getMaxLogPage(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void {
        PlayerBan::getInstance()->getDataManager()->getLogCount(function(array $rows) use ($onSuccess, $limit) {
            $rowCount = (int) $rows[0]['COUNT(*)'];
            $sites = $rowCount / $limit;
            if(($rowCount % $limit) != 0)
                $sites += 1;
            $onSuccess((int) floor($sites));
        }, $onFailure, $limit);
    }

}
