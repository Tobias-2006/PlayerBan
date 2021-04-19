<?php

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\log\Log;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

/**
 * This class regulates the forms for the management of punishments
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
        if(is_null($punishments)) {
            $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
            return;
        }
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
            if(is_null($pun->save())) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                return;
            }

            $log = new Log();
            $log->type = Log::TYPE_CREATION;
            $log->message = PlayerBan::getInstance()->getLang()->translateString("logger.punishment.creation", [$id, $player->getName()]);
            $log->moderator = $player->getName();
            $log->timestamp = time();
            if(is_null($log->save())) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                return;
            }

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
                if(is_null($pun->delete())) {
                    $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                    return;
                }
                $log = new Log();
                $log->type = Log::TYPE_DELETION;
                $log->message = PlayerBan::getInstance()->getLang()->translateString("logger.punishment.deletion", [$pun->id, $player->getName()]);
                $log->moderator = $player->getName();
                $log->timestamp = time();
                if(is_null($log->save())) {
                    $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                    return;
                }
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
            if(is_null($pun->update())) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                return;
            }

            $log = new Log();
            $log->type = Log::TYPE_ADAPTATION;
            $log->message = PlayerBan::getInstance()->getLang()->translateString("logger.punishment.adaptation", [$pun->id, $player->getName()]);
            $log->moderator = $player->getName();
            $log->timestamp = time();
            if(is_null($log->save())) {
                $player->sendMessage(C::RED . PlayerBan::getInstance()->getLang()->translateString("command.error"));
                return;
            }

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
