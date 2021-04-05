<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

/**
 * This class regulates the forms for the management of punishments
 * TODO: Add the creation of log entries in case of changes
 *
 * Class PunishmentForm
 * @package tobias14\playerban\forms
 */
class PunishmentForm {

    /**
     * Main Form -> Will be opened when the /punishments command is executed
     *
     * @param Player $player
     */
    public static function openMainForm(Player $player) {
        $punishments = PlayerBan::getInstance()->getAllPunishments();
        $form = new SimpleForm(function (Player $player, $data) use($punishments) {
            if(is_null($data)) return;
            if($data === count($punishments)) {
                self::openNewPUNForm($player);
            } else {
                self::openEditPUNForm($player, $punishments[$data]);
            }
        });
        $form->setTitle("Punishments");
        foreach ($punishments as $punishment) {
            $form->addButton("ID: {$punishment['id']}\nDESC: {$punishment['description']}");
        }
        $form->addButton("New Punishment");
        $player->sendForm($form);
    }

    /**
     * This form can be used to create new punishments
     *
     * @param Player $player
     */
    public static function openNewPUNForm(Player $player) {
        $form = new CustomForm(function (Player $player, $data) {
            if(is_null($data)) return;
            $id = &$data[0];
            $description = &$data[1];
            $duration = &$data[2];

            if(!is_numeric($id)) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.incorrectFormat", ["id", "3 (0 to 999)"]));
                return;
            }
            $id = round($id);
            if(PlayerBan::getInstance()->punishmentExists($id)) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("db.entry.alreadyExists", ["with the ID {$id}"]));
                return;
            }
            if((int) $id > 999) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.tooLong", ["id", "3"]));
                return;
            }
            if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $duration)) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.incorrectFormat", ["duration", "1d,12h Example2: 2d,5h,30m Example3: 30m"]));
                return;
            }
            if(strlen($description) > 255 or strlen($description) < 3) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.tooLong", ["description", "3 to 255"]));
                return;
            }

            $pun = new Punishment();
            $pun->id = $id;
            $pun->description = $description;
            $pun->duration = Converter::str_to_seconds($duration);
            $pun->save();

            $player->sendMessage(PlayerBan::getInstance()->getLang()->translateString("command.punishments.new.success", [$id]));
        });
        $form->setTitle("New Punishment");
        $form->addInput("Unique identifier:", "0");
        $form->addInput("Description:", "Hacking...");
        $form->addInput("Duration:", "1d,12h");
        $player->sendForm($form);
    }

    /**
     * This form can be used to edit or delete punishments
     *
     * @param Player $player
     * @param $punishment
     */
    public static function openEditPUNForm(Player $player, $punishment) {
        $form = new CustomForm(function (Player $player, $data) use($punishment) {
            if(is_null($data)) return;
            $pun = new Punishment($punishment['id'], $punishment['duration'], $punishment['description']);
            $description = &$data["desc"];
            $duration = &$data[2];

            if($data[3]) {
                $pun->delete();
                $player->sendMessage(PlayerBan::getInstance()->getLang()->translateString("command.punishments.delete.success", [$pun->id]));
                return;
            }
            if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $duration)) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.incorrectFormat", ["duration", "1d,12h Example2: 2d,5h,30m Example3: 30m"]));
                return;
            }
            if(strlen($description) > 255 || strlen($description) < 3) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.param.tooLong", ["description", "3 to 255"]));
                return;
            }

            $pun->description = $description;
            $pun->duration = Converter::str_to_seconds($duration);
            $pun->update();

            $player->sendMessage(PlayerBan::getInstance()->getLang()->translateString("command.punishments.edit.success", [$pun->id]));
        });
        $form->setTitle("Edit Punishment");
        $form->addLabel("» UNIQUE IDENTIFIER: {$punishment['id']}", "desc");
        $form->addInput("Description:", "Hacking...", $punishment['description'], "desc");
        $form->addInput("Duration:", "1d,12h", Converter::seconds_to_str((int) $punishment['duration']));
        $form->addToggle("[!] Delete Punishment");
        $player->sendForm($form);
    }

}
