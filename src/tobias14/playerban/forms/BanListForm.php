<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\utils\Converter;

/**
 * This class manages the banlist forms.
 *
 * Class BanListForm
 * @package tobias14\playerban\forms
 */
class BanListForm extends BaseForm {

    /**
     * This form shows all current bans.
     *
     * @param Player $player
     * @param int $site
     */
    public static function openMainForm(Player $player, int $site = 0) {
        $bans = self::getDataMgr()->getAllCurrentBans($site);
        $form = new SimpleForm(function(Player $player, $data) use ($bans, $site) {
            if(is_null($data)) return;
            if(count($bans) === $data) {
                self::openMainForm($player, ($site + 1));
                return;
            }
            self::openBanInfoForm($player, $bans[$data], $site);
        });
        $form->setTitle(self::translate("banlist.form.title"));
        foreach ($bans as $ban) {
            $creation = date("d.m.Y | H:i", $ban['creation_time']);
            $form->addButton(self::translate("banlist.form.button", [$creation, $ban['target']]));
        }
        if(self::getDataMgr()->getMaxBanPage() > ($site + 1)) {
            $form->addButton(self::translate("button.nextPage"));
        }
        $player->sendForm($form);
    }

    /**
     * This form displays information about a selected ban.
     *
     * @param Player $player
     * @param array $ban
     * @param int $site
     */
    private static function openBanInfoForm(Player $player, array $ban, int $site) {
        $form = new SimpleForm(function (Player $player, $data) use($site, $ban) {
            if(is_null($data)) return;
            if($data === 0) {
                $player->getServer()->dispatchCommand($player, "unban {$ban['target']}");
                return;
            }
            self::openMainForm($player, $site);
        });
        $form->setTitle(self::translate("banlist.form2.title"));
        $form->setContent(self::getBanInfoContent($ban));
        $form->addButton(C::RED . self::translate("banlist.form2.button"));
        $form->addButton(self::translate("button.back"));
        $player->sendForm($form);
    }

    /**
     * Returns a string, with the information lines
     *
     * @param array $ban
     * @return string
     */
    private static function getBanInfoContent(array $ban) : string {
        $data = [];
        $params = [$ban['id'], self::formatTime($ban['creation_time']), $ban['target'], $ban['moderator'], self::formatTime($ban['expiry_time']), $ban['pun_id']];
        for ($i = 0; $i < 8; $i++) {
            $line = $i + 1;
            if($i === 6) {
                if(self::getDataMgr()->punishmentExists($ban['pun_id'])) {
                    $punishment = self::getDataMgr()->getPunishment($ban['pun_id']);
                    $params[] = $punishment['description'];
                    $params[] = Converter::seconds_to_str($punishment['duration']);
                } else{
                    break;
                }
            }
            $str = self::translate("banlist.form2.line$line", [$params[$i]]);
            if($i === 0)
                $str .= "\n";
            $data[] = $str;
        }
        return implode("\n", $data);
    }

}
