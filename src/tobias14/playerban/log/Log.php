<?php

namespace tobias14\playerban\log;

/**
 * This class represents the log instance
 *
 * Class Log
 * @package tobias14\playerban\log
 */
class Log {

    public const TYPE_CREATION = 0;
    public const TYPE_DELETION = 1;
    public const TYPE_ADAPTATION = 2;

    /** @var int $type */
    public $type;
    /** @var string $message */
    public $message;
    /** @var string $moderator */
    public $moderator;
    /** @var int $timestamp */
    public $timestamp;

    /**
     * Log constructor.
     *
     * @param int $type
     * @param string $message
     * @param string $moderator
     * @param int $timestamp
     */
    public function __construct(int $type = -1, string $message = "", string $moderator = "", int $timestamp = -1) {
        $this->type = $type;
        $this->message = $message;
        $this->moderator = $moderator;
        $this->timestamp = $timestamp;
    }

    /**
     * @return bool
     */
    public function isValidType() : bool {
        switch ($this->type) {
            case self::TYPE_CREATION:
            case self::TYPE_DELETION:
            case self::TYPE_ADAPTATION:
                return true;
            default:
                return false;
        }
    }

    /**
     * @return null|bool
     */
    public function save() : ?bool {
        if(!$this->isValidType()) return false;
        return Logger::log($this);
    }

}
