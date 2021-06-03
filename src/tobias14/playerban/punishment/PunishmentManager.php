<?php
declare(strict_types=1);

namespace tobias14\playerban\punishment;

use tobias14\playerban\PlayerBan;

class PunishmentManager {

    /** @var PlayerBan $plugin */
    protected $plugin;

    /**
     * PunishmentManager constructor.
     *
     * @param PlayerBan $plugin
     */
    public function __construct(PlayerBan $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Create a new punishment
     *
     * @param Punishment $punishment
     * @return bool
     */
    public function create(Punishment $punishment) : bool {
        if($this->exists($punishment->id))
            return false;
        if(!$punishment->isValidId())
            return false;
        return $this->plugin->getDataManager()->savePunishment($punishment) ?? false;
    }

    /**
     * Delete a new punishment
     *
     * @param Punishment $punishment
     * @return bool
     */
    public function delete(Punishment $punishment) : bool {
        if(!$this->exists($punishment->id))
            return false;
        return $this->plugin->getDataManager()->deletePunishment($punishment) ?? false;
    }

    /**
     * Update a punishment
     *
     * @param Punishment $punishment
     * @return bool
     */
    public function update(Punishment $punishment) : bool {
        if(!$this->exists($punishment->id))
            return false;
        return $this->plugin->getDataManager()->updatePunishment($punishment) ?? false;
    }

    /**
     * Check if a punishment exists
     *
     * @param int $id
     * @return bool|null
     */
    public function exists(int $id) : ?bool {
        return $this->plugin->getDataManager()->punishmentExists($id);
    }

    /**
     * Returns a punishment instance if the punishment exists
     *
     * @param int $id
     * @return Punishment|null
     */
    public function get(int $id) : ?Punishment {
        return $this->plugin->getDataManager()->getPunishment($id);
    }

    /**
     * Returns a list of all punishments
     *
     * @return Punishment[]|null
     */
    public function getAll() : ?array {
        return $this->plugin->getDataManager()->getAllPunishments();
    }

}
