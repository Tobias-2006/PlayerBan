<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class DeletionLog extends Log {

    /**
     * DeletionLog constructor.
     *
     * @param string $description
     * @param string $moderator
     * @param string $target
     */
    public function __construct(string $description, string $moderator, string $target) {
        parent::__construct($description, $moderator, $target);
        $this->type = Logger::LOG_TYPE_DELETION;
        $this->creationTime = time();
    }

}
