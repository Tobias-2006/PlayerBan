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

    /**
     * Creates a new log and saves it into the database
     *
     * @param Log $log
     * @return bool|null
     */
    public static function log(Log $log) : ?bool {
        return PlayerBan::getInstance()->getDataManager()->saveLog(
            $log->type,
            $log->message,
            $log->moderator,
            $log->timestamp
        );
    }

}
