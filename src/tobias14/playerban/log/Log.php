<?php

namespace tobias14\playerban\log;

/**
 * This class represents the log instance
 *
 * Class Log
 * @package tobias14\playerban\log
 */
abstract class Log {

    /** @var int $type */
    protected $type;
    /** @var int $creation_time */
    protected $creation_time;

    /** @var string $description */
    public $description;
    /** @var string $moderator */
    public $moderator;
    /** @var string $target */
    public $target;

    /**
     * @return int
     */
    public function getType() : int {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getCreationTime() : int {
        return $this->creation_time;
    }

    /**
     * @return null|bool
     */
    public function save() : ?bool {
        return Logger::log($this);
    }

}
