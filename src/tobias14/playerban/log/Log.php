<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

abstract class Log {

    /** @var int $type */
    protected $type;
    /** @var string $description */
    protected $description;
    /** @var string $moderator */
    protected $moderator;
    /** @var string $target */
    protected $target;
    /** @var int $creationTime */
    protected $creationTime;

    /**
     * Log constructor.
     *
     * @param string $description
     * @param string $moderator
     * @param string $target
     */
    public function __construct(string $description, string $moderator, string $target) {
        $this->description = $description;
        $this->moderator = $moderator;
        $this->target = $target;
    }

    /**
     * Types: LOG_TYPE_CREATION, LOG_TYPE_DELETION, LOG_TYPE_ADAPTATION | (Logger.php)
     *
     * @return int
     */
    public function getType() : int {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getModerator() : string {
        return $this->moderator;
    }

    /**
     * @return string
     */
    public function getTarget() : string {
        return $this->target;
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
