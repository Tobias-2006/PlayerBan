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
    /** @var int $creationTime */
    protected $creationTime;

    /** @var string $description */
    public $description;
    /** @var string $moderator */
    public $moderator;
    /** @var string $target */
    public $target;

    /**
     * Types: LOG_TYPE_CREATION, LOG_TYPE_DELETION, LOG_TYPE_ADAPTATION | (Logger.php)
     *
     * @return int
     */
    public function getType() : int {
        return $this->type;
    }

    /**
     * Current Unix-Timestamp
     *
     * @return int
     */
    public function getCreationTime() : int {
        return $this->creationTime;
    }

    /**
     * Saves the log to the database
     *
     * @return null|bool
     */
    public function save() : ?bool {
        return Logger::log($this);
    }

}
