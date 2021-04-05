<?php

namespace tobias14\playerban\punishment;

use tobias14\playerban\database\Database;
use tobias14\playerban\PlayerBan;

/**
 * This class represents the instance of a punishment
 *
 * Class Punishment
 * @package tobias14\playerban\punishment
 */
class Punishment {

    /** @var int $id */
    public $id;
    /** @var int $duration */
    public $duration;
    /** @var string $description */
    public $description;

    public function __construct(int $id = -1, int $duration = -1, string $description = "") {
        $this->id = $id;
        $this->duration = $duration;
        $this->description = $description;
    }

    /**
     * Saves a new punishment to the database
     *
     * @return bool
     */
    public function save() : bool {
        if($this->id === -1 or $this->duration === -1 or $this->description === "") return false;
        $database = Database::connect();
        $database->query("INSERT INTO punishments(id, duration, description) VALUES('{$this->id}', '{$this->duration}', '{$this->description}')");
        $database->close();
        return true;
    }

    /**
     * Deletes the punishment from the database if it exists
     *
     * @return bool
     */
    public function delete() : bool {
        $database = Database::connect();
        if(!PlayerBan::getInstance()->punishmentExists($this->id)) return false;
        $database->query("DELETE FROM punishments WHERE id='{$this->id}'");
        $database->close();
        return true;
    }

    /**
     * Saves changes to the database
     *
     * @return bool
     */
    public function update() : bool {
        $database = Database::connect();
        if(!PlayerBan::getInstance()->punishmentExists($this->id)) return false;
        $database->query("UPDATE punishments SET duration='{$this->duration}', description='{$this->description}' WHERE id='{$this->id}'");
        $database->close();
        return true;
    }

}
