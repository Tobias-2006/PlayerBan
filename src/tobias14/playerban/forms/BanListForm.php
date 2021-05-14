<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\utils\Converter;

/**
 * Class BanListForm
 * @package tobias14\playerban\forms
 */
class BanListForm {

    /**
     * @param Player $player
     * @param int $site
     */
    public static function openMainForm(Player $player, $site = 0) {
        $bans = PlayerBan::getInstance()->getDataManager()->getAllCurrentBans($site);
        $form = new SimpleForm(function(Player $player, $data) use ($bans, $site) {
            if(is_null($data)) return;
            if(count($bans) === $data) {
                self::openMainForm($player, ($site + 1));
                return;
            }
            self::openBanInfoForm($player, $bans[$data], $site);
        });
        $form->setTitle("BanList");
        foreach ($bans as $ban) {
            $title = date("d.m.Y | H:i", $ban['creation_time']) . "\nTarget: " . $ban['target'];
            $form->addButton($title);
        }
        if(PlayerBan::getInstance()->getDataManager()->getMaxBanPage() > ($site + 1)) {
            $form->addButton("Next Page");
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param array $ban
     * @param int $site
     */
    public static function openBanInfoForm(Player $player, array $ban, int $site) {
        $form = new SimpleForm(function (Player $player, $data) use($site, $ban) {
            if(is_null($data)) return;
            if($data === 0) {
                $player->getServer()->dispatchCommand($player, "unban {$ban['target']}");
                return;
            }
            self::openMainForm($player, $site);
        });
        $form->setTitle("BanInfo");
        $ban_creation = date("d.m.Y | H:i", $ban['creation_time']);
        $expiry_time = date("d.m.Y | H:i", $ban['expiry_time']);
        $content = "» UNIQUE IDENTIFIER: {$ban['id']}\nCreation: {$ban_creation}\nTarget: {$ban['target']}\nModerator: {$ban['moderator']}\nExpiry: {$expiry_time}\nPunId: {$ban['pun_id']}";
        if(PlayerBan::getInstance()->getDataManager()->punishmentExists($ban['pun_id'])) {
            $punishment = PlayerBan::getInstance()->getDataManager()->getPunishment($ban['pun_id']);
            $duration = Converter::seconds_to_str($punishment['duration']);
            $content .= "\nReason: {$punishment['description']}\nDuration: $duration";
        }
        $form->setContent($content);
        $form->addButton(C::RED . "Unban");
        $form->addButton("Back");
        $player->sendForm($form);
    }

}
