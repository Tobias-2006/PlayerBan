<?php

namespace tobias14\playerban\ban;

use tobias14\playerban\database\Database;

/**
 * This class represents the ban instance
 *
 * Class Ban
 * @package tobias14\playerban\ban
 */
class Ban {

    /** @var string $target */
    private $target;
    /** @var int $duration */
    private $duration;
    /** @var int $timestamp */
    private $timestamp;

    public function __construct($target = "", $duration = -1, $timestamp = -1) {
        $this->target = $target;
        $this->duration = $duration;
        $this->timestamp = $timestamp;
    }

    /**
     * Saving to the database
     *
     * @return void
     */
    public function save() {
        if($this->target === "" or $this->duration === -1 or $this->timestamp === -1) return;
        $database = Database::connect();
        $database->query("INSERT INTO bans(target, duration, timestamp) VALUES('{$this->target}', '{$this->duration}', '{$this->timestamp}')");
        $database->close();
    }

}