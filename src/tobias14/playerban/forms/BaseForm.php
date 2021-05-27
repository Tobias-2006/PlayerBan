<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;

abstract class BaseForm {

    /**
     * Massage Management
     *
     * @param string $str
     * @param array $params
     * @return string
     */
    protected static function translate(string $str, array $params = []) : string {
        return PlayerBan::getInstance()->getLang()->translateString($str, $params);
    }

    /**
     * Database Management
     *
     * @return DataManager
     */
    protected static function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

    /**
     * @param int $timestamp
     * @return string
     */
    protected static function formatTime(int $timestamp) : string {
        return PlayerBan::getInstance()->formatTime($timestamp);
    }

}
