<?php

namespace tobias14\playerban\ban;

use tobias14\playerban\database\Database;

/**
 * This class represents the unchecked-ban instance
 *
 * Class UncheckedBan
 * @package tobias14\playerban\ban
 */
class UncheckedBan {

    /** @var string $target */
    private $target;
    /** @var int $duration */
    private $duration;
    /** @var int $timestamp */
    private $timestamp;
    /** @var string $moderator */
    private $moderator;
    /** @var string $reason */
    private $reason;

    public function __construct(string $target = "", $duration = -1, $timestamp = -1, $moderator = "", $reason = "") {
        $this->target = $target;
        $this->duration = $duration;
        $this->timestamp = $timestamp;
        $this->moderator = $moderator;
        $this->reason = $reason;
    }

    /**
     * Saving to the database
     *
     * @return void
     */
    public function save() {
        if($this->target === "" or $this->duration === -1 or $this->timestamp === -1 or $this->moderator === "" or $this->reason === "") return;
        $database = Database::connect();
        $database->query("INSERT INTO pending(target, duration, timestamp, moderator, reason) VALUES('{$this->target}', '{$this->duration}', '{$this->timestamp}', '{$this->moderator}', '{$this->reason}')");
        $database->close();
    }

}