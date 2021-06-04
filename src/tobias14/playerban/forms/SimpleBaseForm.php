<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use tobias14\playerban\ban\BanManager;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\PunishmentManager;

abstract class SimpleBaseForm extends SimpleForm {

    /**
     * SimpleBaseForm constructor.
     *
     * @param callable|null $callable
     */
    public function __construct(?callable $callable) {
        parent::__construct($callable);
    }

    /**
     * Massage Management
     *
     * @param string $str
     * @param int[]|float[]|string[] $params
     * @return string
     */
    protected function translate(string $str, array $params = []) : string {
        return PlayerBan::getInstance()->getLanguage()->translateString($str, $params);
    }

    /**
     * Database Management
     *
     * @return DataManager
     */
    protected function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

    /**
     * Ban Management
     *
     * @return BanManager
     */
    protected function getBanMgr() : BanManager {
        return PlayerBan::getInstance()->getBanManager();
    }

    /**
     * Punishment Management
     *
     * @return PunishmentManager
     */
    protected function getPunishmentMgr() : PunishmentManager {
        return PlayerBan::getInstance()->getPunishmentManager();
    }

    /**
     * @param int $timestamp
     * @return string
     */
    protected function formatTime(int $timestamp) : string {
        return PlayerBan::getInstance()->formatTime($timestamp);
    }

}
