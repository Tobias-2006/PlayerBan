<?php

namespace tobias14\playerban\forms;

use pocketmine\lang\BaseLang;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\PlayerBan;

/**
 * Class BaseForm
 *
 * @package tobias14\playerban\forms
 */
abstract class BaseForm {

    /**
     * @return BaseLang
     */
    protected static function getLang() : BaseLang {
        return PlayerBan::getInstance()->getLang();
    }

    /**
     * @return DataManager
     */
    protected static function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

}
