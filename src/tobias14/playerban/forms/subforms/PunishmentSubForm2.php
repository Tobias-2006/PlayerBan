<?php
declare(strict_types=1);

namespace tobias14\playerban\forms\subforms;

use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use tobias14\playerban\forms\CustomBaseForm;
use tobias14\playerban\log\Log;
use tobias14\playerban\log\Logger;
use tobias14\playerban\punishment\Punishment;
use tobias14\playerban\utils\Converter;

class PunishmentSubForm2 extends CustomBaseForm {

    /**
     * PunishmentSubForm2 constructor.
     *
     * @param Punishment $punishment
     */
    public function __construct(Punishment $punishment) {
        parent::__construct($this->onCall($punishment));
        $this->setTitle($this->translate("punishments.form3.title"));
        $this->addLabel($this->translate("punishments.form3.label", [$punishment->id]), "desc");
        $this->addInput($this->translate("punishments.form3.input"), "Hacking...", $punishment->description, "desc");
        $this->addInput($this->translate("punishments.form3.input2"), "1d,12h", Converter::secondsToStr($punishment->duration));
        $this->addToggle($this->translate("punishments.form3.toggle"));
    }

    /**
     * @param Punishment $punishment
     * @return callable
     */
    protected function onCall(Punishment $punishment) : callable {
        return function (Player $player, $data) use ($punishment) {
            if(is_null($data)) return;
            $description = &$data["desc"];
            $duration = &$data[2];
            if($data[3]) {
                if($this->getPunishmentMgr()->delete($punishment)) {
                    $player->sendMessage(C::RED . $this->translate("error"));
                    return;
                }
                $log = new Log(Logger::LOG_TYPE_DELETION, $this->translate("logger.punishment.deletion"), $player->getName(), "PunId[" . $punishment->id . "]");
                if(!Logger::getLogger()->log($log)) {
                    $player->sendMessage(C::RED . $this->translate("error"));
                    return;
                }
                $player->sendMessage($this->translate("punishments.delete.success", [$punishment->id]));
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
            $punishment->description = $description;
            $punishment->duration = Converter::strToSeconds($duration);
            if(!$this->getPunishmentMgr()->update($punishment)) {
                $player->sendMessage(C::RED . $this->translate("error"));
                return;
            }
            $log = new Log(Logger::LOG_TYPE_ADAPTATION, $this->translate("logger.punishment.adaptation"), $player->getName(), "PunId[" . $punishment->id . "]");
            if(!Logger::getLogger()->log($log)) {
                $player->sendMessage(C::RED . $this->translate("error"));
                return;
            }
            $player->sendMessage($this->translate("punishments.edit.success", [$punishment->id]));
        };
    }

}
