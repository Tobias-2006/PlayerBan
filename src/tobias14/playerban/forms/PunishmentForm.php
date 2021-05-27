<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\log\AdaptationLog;
use tobias14\playerban\log\CreationLog;
use tobias14\playerban\log\DeletionLog;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

class PunishmentForm extends BaseForm {

    /**
     * Main Form -> Will be opened when the /punishments command is executed
     *
     * @param Player $player
     */
    public static function openMainForm(Player $player) : void {
        $punishments = self::getDataMgr()->getAllPunishments();
        if(is_null($punishments)) {
            $player->sendMessage(C::RED . self::translate("error"));
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
        $form->setTitle(self::translate("punishment.form.title"));
        foreach ($punishments as $punishment) {
            $form->addButton(self::translate("punishment.form.button", [$punishment['id'], $punishment['description']]));
        }
        $form->addButton(self::translate("punishment.form.button2"));
        $player->sendForm($form);
    }

    /**
     * This form can be used to create new punishments
     *
     * @param Player $player
     */
    public static function openNewPUNForm(Player $player) : void {
        $form = new CustomForm(function (Player $player, $data) {
            if(is_null($data)) return;
            $id = &$data[0];
            $description = &$data[1];
            $duration = &$data[2];

            if(!is_numeric($id)) {
                $player->sendMessage(C::RED . self::translate("param.incorrect", ["id", "3 (0 to 999)"]));
                return;
            }
            $id = round($id);
            if(self::getDataMgr()->punishmentExists((int) $id)) {
                $player->sendMessage(C::RED . self::translate("punishment.exist", [$id]));
                return;
            }
            if((int) $id > 999) {
                $player->sendMessage(C::RED . self::translate("param.tooLong", ["id", "3"]));
                return;
            }
            if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $duration)) {
                $player->sendMessage(C::RED . self::translate("param.incorrect", ["duration", "1d,12h Example2: 2d,5h,30m Example3: 30m"]));
                return;
            }
            if(strlen($description) > 255 or strlen($description) < 3) {
                $player->sendMessage(C::RED . self::translate("param.tooLong", ["description", "3 to 255"]));
                return;
            }

            $pun = new Punishment();
            $pun->id = (int) $id;
            $pun->description = $description;
            $pun->duration = Converter::strToSeconds($duration);
            if(is_null($pun->save())) {
                $player->sendMessage(C::RED . self::translate("error"));
                return;
            }

            $log = new CreationLog();
            $log->description = self::translate("logger.punishment.creation");
            $log->moderator = $player->getName();
            $log->target = "PunId[" . $pun->id . "]";
            if(is_null($log->save())) {
                $player->sendMessage(C::RED . self::translate("error"));
                return;
            }

            $player->sendMessage(self::translate("punishments.new.success", [$id]));
        });
        $form->setTitle(self::translate("punishment.form.button2"));
        $form->addInput(self::translate("punishment.form2.input"), "0");
        $form->addInput(self::translate("punishment.form2.input2"), "Hacking...");
        $form->addInput(self::translate("punishment.form2.input3"), "1d,12h");
        $player->sendForm($form);
    }

    /**
     * This form can be used to edit or delete punishments
     *
     * @param Player $player
     * @param string[]|int[] $punishment
     */
    public static function openEditPUNForm(Player $player, array $punishment) : void {
        $form = new CustomForm(function (Player $player, $data) use($punishment) {
            if(is_null($data)) return;
            $pun = new Punishment($punishment['id'], $punishment['duration'], $punishment['description']);
            $description = &$data["desc"];
            $duration = &$data[2];

            if($data[3]) {
                if(is_null($pun->delete())) {
                    $player->sendMessage(C::RED . self::translate("error"));
                    return;
                }
                $log = new DeletionLog();
                $log->description = self::translate("logger.punishment.deletion");
                $log->moderator = $player->getName();
                $log->target = "PunId[" . $pun->id . "]";
                if(is_null($log->save())) {
                    $player->sendMessage(C::RED . self::translate("error"));
                    return;
                }
                $player->sendMessage(self::translate("punishments.delete.success", [$pun->id]));
                return;
            }
            if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $duration)) {
                $player->sendMessage(C::RED . self::translate("param.incorrect", ["duration", "1d,12h Example2: 2d,5h,30m Example3: 30m"]));
                return;
            }
            if(strlen($description) > 255 || strlen($description) < 3) {
                $player->sendMessage(C::RED . self::translate("param.tooLong", ["description", "3 to 255"]));
                return;
            }

            $pun->description = $description;
            $pun->duration = Converter::strToSeconds($duration);
            if(is_null($pun->update())) {
                $player->sendMessage(C::RED . self::translate("error"));
                return;
            }

            $log = new AdaptationLog();
            $log->description = self::translate("logger.punishment.adaptation");
            $log->moderator = $player->getName();
            $log->target = "PunId[" . $pun->id . "]";
            if(is_null($log->save())) {
                $player->sendMessage(C::RED . self::translate("error"));
                return;
            }

            $player->sendMessage(self::translate("punishments.edit.success", [$pun->id]));
        });
        $form->setTitle(self::translate("punishment.form3.title"));
        $form->addLabel(self::translate("punishment.form3.label", [$punishment['id']]), "desc");
        $form->addInput(self::translate("punishment.form3.input"), "Hacking...", $punishment['description'], "desc");
        $form->addInput(self::translate("punishment.form3.input2"), "1d,12h", Converter::secondsToStr((int) $punishment['duration']));
        $form->addToggle(self::translate("punishment.form3.toggle"));
        $player->sendForm($form);
    }

}
