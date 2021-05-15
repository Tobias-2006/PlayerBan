<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

/**
 * This class manages the log forms
 *
 * Class BanLogsForm
 * @package tobias14\playerban\forms
 */
class BanLogsForm extends BaseForm {

    /**
     * This form lists all logs.
     *
     * @param Player $player
     * @param int $site
     */
    public static function openMainForm(Player $player, int $site = 0) {
        $logs = self::getDataMgr()->getLogs($site);
        $form = new SimpleForm(function (Player $player, $data) use ($logs, $site) {
            if(is_null($data)) return;
            if($data === (count($logs))) {
                self::openMainForm($player, ($site + 1));
                return;
            }
            self::openLogInfoForm($player, $logs[$data], $site);
        });
        $form->setTitle(self::translate("banlogs.form.title"));
        foreach ($logs as $log) {
            $form->addButton(self::translate("banlogs.form.button", [self::formatTime($log['creation_time']), $log['moderator']]));
        }
        if(self::getDataMgr()->getMaxLogPage() > ($site + 1)) {
            $form->addButton(self::translate("button.nextPage"));
        }
        $player->sendForm($form);
    }

    /**
     * This form shows information about a log.
     *
     * @param Player $player
     * @param array $log
     * @param $site
     */
    private static function openLogInfoForm(Player $player, array $log, $site) {
        $form = new SimpleForm(function (Player $player, $data) use ($site) {
            if(is_null($data)) return;
            if(0 === $data) {
                self::openMainForm($player, $site);
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
     * @param array $log
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
