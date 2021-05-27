<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class AdaptationLog extends Log {

    public function __construct() {
        $this->type = Logger::LOG_TYPE_ADAPTATION;
        $this->creationTime = time();
    }

}
