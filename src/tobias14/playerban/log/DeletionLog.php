<?php

namespace tobias14\playerban\log;

/**
 * Class DeletionLog
 * @package tobias14\playerban\log
 */
class DeletionLog extends Log {

    public function __construct() {
        $this->type = Logger::LOG_TYPE_DELETION;
        $this->creation_time = time();
    }

}
