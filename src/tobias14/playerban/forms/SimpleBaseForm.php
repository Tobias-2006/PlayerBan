<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\SimpleForm;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;

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
        return PlayerBan::getInstance()->getLang()->translateString($str, $params);
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
     * @param int $timestamp
     * @return string
     */
    protected function formatTime(int $timestamp) : string {
        return PlayerBan::getInstance()->formatTime($timestamp);
    }

}
