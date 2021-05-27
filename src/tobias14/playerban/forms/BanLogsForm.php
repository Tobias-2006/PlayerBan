<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class BanLogsForm extends BaseForm {

    /**
     * This form lists all logs.
     *
     * @param Player $player
     * @param int $page
     */
    public static function openMainForm(Player $player, int $page = 0) : void {
        $logs = self::getDataMgr()->getLogs($page);
        $form = new SimpleForm(function (Player $player, $data) use ($logs, $page) {
            if(is_null($data)) return;
            if($data === (count($logs))) {
                self::openMainForm($player, ($page + 1));
                return;
            }
            self::openLogInfoForm($player, $logs[$data], $page);
        });
        $form->setTitle(self::translate("banlogs.form.title"));
        foreach ($logs as $log) {
            $form->addButton(self::translate("banlogs.form.button", [self::formatTime($log['creation_time']), $log['moderator']]));
        }
        if(self::getDataMgr()->getMaxLogPage() > ($page + 1)) {
            $form->addButton(self::translate("button.nextPage"));
        }
        $player->sendForm($form);
    }

    /**
     * This form shows information about a log.
     *
     * @param Player $player
     * @param string[]|int[] $log
     * @param int $page
     */
    private static function openLogInfoForm(Player $player, array $log, int $page) : void {
        $form = new SimpleForm(function (Player $player, $data) use ($page) {
            if(is_null($data)) return;
            if(0 === $data) {
                self::openMainForm($player, $page);
            }
        });
        $form->setTitle(self::translate("banlogs.form2.title"));
        $form->setContent(self::getLogInfoContent($log));
        $form->addButton(self::translate("button.back"));
        $player->sendForm($form);
    }

    /**
     * Returns a string, with the information lines
     *
     * @param string[]|int[] $log
     * @return string
     */
    private static function getLogInfoContent(array $log) : string {
        $data = [];
        $params = [$log['type'], $log['description'], $log['moderator'], $log['target'], self::formatTime($log['creation_time'])];
        for($i = 0; $i < 5; $i++) {
            $line = $i + 1;
            $data[] = self::translate("banlogs.form2.line$line", [$params[$i]]);
        }
        return implode("\n", $data);
    }

}
