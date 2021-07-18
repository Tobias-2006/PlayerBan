<?php
declare(strict_types=1);

namespace tobias14\playerban\punishment;

class Punishment {

    /** @var int $id */
    public $id;
    /** @var int $duration */
    public $duration;
    /** @var string $description */
    public $description;

    /**
     * Punishment constructor.
     *
     * @param int $id
     * @param int $duration
     * @param string $description
     */
    public function __construct(int $id, int $duration, string $description) {
        $this->id = $id;
        $this->duration = $duration;
        $this->description = $description;
    }

    /**
     * Check if the id is valid
     *
     * @return bool
     */
    public function hasValidId() : bool {
        return is_int($this->id) and $this->id >= 0 and $this->id <= 999;
    }

}
