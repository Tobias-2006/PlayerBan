<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

class Log {

    /** @var int $type */
    public $type;
    /** @var string $description */
    public $description;
    /** @var string $moderator */
    public $moderator;
    /** @var string $target */
    public $target;
    /** @var int|null $creationTime */
    public $creationTime;

    /**
     * Log constructor.
     *
     * @param int $type
     * @param string $description
     * @param string $moderator
     * @param string $target
     * @param int|null $creationTime
     */
    public function __construct(int $type, string $description, string $moderator, string $target, ?int $creationTime = null) {
        $this->type = $type;
        $this->description = $description;
        $this->moderator = $moderator;
        $this->target = $target;
        $this->creationTime = $creationTime;
    }

    /**
     * Check if the type is valid
     *
     * @return bool
     */
    public function hasValidType() : bool {
        switch ($this->type) {
            case Logger::LOG_TYPE_CREATION:
            case Logger::LOG_TYPE_DELETION:
            case Logger::LOG_TYPE_ADAPTATION:
                return true;
            default:
                return false;
        }
    }

}
