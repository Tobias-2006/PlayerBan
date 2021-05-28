<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\CustomBaseForm;
use tobias14\playerban\log\AdaptationLog;
use tobias14\playerban\log\DeletionLog;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

class PunishmentSubForm2 extends CustomBaseForm {

    /**
     * PunishmentSubForm2 constructor.
     *
     * @param string[]|int[] $punishment
     */
    public function __construct(array $punishment) {
        parent::__construct($this->onCall($punishment));
        $this->setTitle($this->translate("punishments.form3.title"));
        $this->addLabel($this->translate("punishments.form3.label", [$punishment['id']]), "desc");
        $this->addInput($this->translate("punishments.form3.input"), "Hacking...", $punishment['description'], "desc");
        $this->addInput($this->translate("punishments.form3.input2"), "1d,12h", Converter::secondsToStr((int) $punishment['duration']));
        $this->addToggle($this->translate("punishments.form3.toggle"));
    }

    /**
     * @param string[]|int[] $punishment
     * @return callable
     */
    protected function onCall(array $punishment) : callable {
        return function (Player $player, $data) use ($punishment) {
            if(is_null($data)) return;
            $pun = new Punishment($punishment['id'], $punishment['duration'], $punishment['description']);
            $description = &$data["desc"];
            $duration = &$data[2];
            if($data[3]) {
                if(is_null($pun->delete())) {
                    $player->sendMessage(C::RED . $this->translate("error"));
                    return;
                }
                $log = new DeletionLog($this->translate("logger.punishment.deletion"), $player->getName(), "PunId[" . $pun->id . "]");
                if(is_null($log->save())) {
                    $player->sendMessage(C::RED . $this->translate("error"));
                    return;
                }
                $player->sendMessage($this->translate("punishments.delete.success", [$pun->id]));
                return;
            }
            if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $duration)) {
                $player->sendMessage(C::RED . $this->translate("param.incorrect", ["duration", "1d,12h Example2: 2d,5h,30m Example3: 30m"]));
                return;
            }
            if(strlen($description) > 255 || strlen($description) < 3) {
                $player->sendMessage(C::RED . $this->translate("param.tooLong", ["description", "3 to 255"]));
                return;
            }
            $pun->description = $description;
            $pun->duration = Converter::strToSeconds($duration);
            if(is_null($pun->update())) {
                $player->sendMessage(C::RED . $this->translate("error"));
                return;
            }
            $log = new AdaptationLog($this->translate("logger.punishment.adaptation"), $player->getName(), "PunId[" . $pun->id . "]");
            if(is_null($log->save())) {
                $player->sendMessage(C::RED . $this->translate("error"));
                return;
            }
            $player->sendMessage($this->translate("punishments.edit.success", [$pun->id]));
        };
    }

}
