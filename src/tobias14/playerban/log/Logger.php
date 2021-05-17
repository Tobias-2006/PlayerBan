<?php

namespace tobias14\playerban\log;

use tobias14\playerban\PlayerBan;

/**
 * This class represents the LogManager
 *
 * Class Logger
 * @package tobias14\playerban\log
 */
class Logger {

    public const LOG_TYPE_CREATION = 0;
    public const LOG_TYPE_DELETION = 1;
    public const LOG_TYPE_ADAPTATION = 2;

    /**
     * Creates a new log and saves it into the database
     *
     * @param Log $log
     * @return bool|null
     */
    public static function log(Log $log) : ?bool {
        return PlayerBan::getInstance()->getDataManager()->saveLog(
            $log->getType(),
            $log->description,
            $log->moderator,
            $log->getCreationTime(),
            $log->target
        );
    }

}
