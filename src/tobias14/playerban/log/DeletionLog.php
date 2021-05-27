<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class DeletionLog extends Log {

    public function __construct() {
        $this->type = Logger::LOG_TYPE_DELETION;
        $this->creationTime = time();
    }

}
