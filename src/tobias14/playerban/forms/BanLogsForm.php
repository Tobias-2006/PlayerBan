<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use tobias14\playerban\PlayerBan;

/**
 * This class manages the log forms
 *
 * Class BanLogsForm
 * @package tobias14\playerban\forms
 */
class BanLogsForm {

    /**
     * @param Player $player
     * @param int $site
     */
    public static function openMainForm(Player $player, $site = 0) {
        $logs = PlayerBan::getInstance()->getDataManager()->getLogs($site);
        $form = new SimpleForm(function (Player $player, $data) use ($logs, $site) {
            if(is_null($data)) return;
            if($data === (count($logs))) {
                self::openMainForm($player, ($site + 1));
                return;
            }
            self::openLogInfoForm($player, $logs[$data], $site);
        });
        $form->setTitle("BanLogs");
        foreach ($logs as $log) {
            $title = date("d.m.Y | H:i", $log['creation_time']) . "\nMOD: " . $log['moderator'];
            $form->addButton($title);
        }
        if(PlayerBan::getInstance()->getDataManager()->getMaxLogPage() > ($site + 1)) {
            $form->addButton("Next Page");
        }
        $player->sendForm($form);
    }

    /**
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
        $log_creation = date("d.m.Y | H:i", $log['creation_time']);
        $information = ["Type: {$log['type']}", "Description: {$log['description']}", "Moderator: {$log['moderator']}", "Target: {$log['target']}", "Creation: {$log_creation}"];
        $form->setTitle("LogInfo");
        $form->setContent(implode("\n", $information));
        $form->addButton("Back");
        $player->sendForm($form);
    }

}
