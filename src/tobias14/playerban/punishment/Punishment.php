<?php

namespace tobias14\playerban\punishment;

use tobias14\playerban\database\DataManager;
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

    /**
     * Punishment constructor.
     *
     * @param int $id
     * @param int $duration
     * @param string $description
     */
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
        return $this->getDataMgr()->savePunishment($this->id, $this->duration, $this->description);
    }

    /**
     * Deletes the punishment from the database if it exists
     *
     * @return bool
     */
    public function delete() : bool {
        if(!PlayerBan::getInstance()->punishmentExists($this->id)) return false;
        return $this->getDataMgr()->deletePunishment($this->id);
    }

    /**
     * Saves changes to the database
     *
     * @return bool
     */
    public function update() : bool {
        if(!PlayerBan::getInstance()->punishmentExists($this->id)) return false;
        return $this->getDataMgr()->updatePunishment($this->id, $this->duration, $this->description);
    }

    /**
     * @return DataManager
     */
    private function getDataMgr() : DataManager {
        return PlayerBan::getInstance()->getDataManager();
    }

}
