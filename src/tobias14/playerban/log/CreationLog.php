<?php

namespace tobias14\playerban\log;

/**
 * Class CreationLog
 *
 * @package tobias14\playerban\log
 */
class CreationLog extends Log {

    public function __construct() {
        $this->type = Logger::LOG_TYPE_CREATION;
        $this->creation_time = time();
    }

}
