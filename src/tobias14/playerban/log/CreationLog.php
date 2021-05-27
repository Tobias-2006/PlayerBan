<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class CreationLog extends Log {

    /**
     * CreationLog constructor.
     *
     * @param string $description
     * @param string $moderator
     * @param string $target
     */
    public function __construct(string $description, string $moderator, string $target) {
        parent::__construct($description, $moderator, $target);
        $this->type = Logger::LOG_TYPE_CREATION;
        $this->creationTime = time();
    }

}
