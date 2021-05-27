<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class CreationLog extends Log {

    public function __construct() {
        $this->type = Logger::LOG_TYPE_CREATION;
        $this->creationTime = time();
    }

}
