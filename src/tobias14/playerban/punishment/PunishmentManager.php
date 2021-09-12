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
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function create(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->exists($punishment->id, function (bool $punExists) use ($punishment, $onSuccess, $onFailure) {
            if($punExists)
                return;
            if(!$punishment->hasValidId())
                throw new InvalidPunishmentIdException('Tried to save a punishment with an invalid id!');
            $this->plugin->getDataManager()->savePunishment(
                $punishment,
                $onSuccess,
                $onFailure
            );
        });
    }

    /**
     * Delete a new punishment
     *
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function delete(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->exists($punishment->id, function (bool $punExists) use ($punishment, $onSuccess, $onFailure) {
            if(!$punExists)
                return;
            $this->plugin->getDataManager()->deletePunishment(
                $punishment,
                $onSuccess,
                $onFailure
            );
        });
    }

    /**
     * Update a punishment
     *
     * @param Punishment $punishment
     * @param callable|null $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function update(Punishment $punishment, callable $onSuccess = null, callable $onFailure = null) : void {
        $this->exists($punishment->id, function (bool $punExists) use ($punishment, $onSuccess, $onFailure) {
            if(!$punExists)
                return;
            $this->plugin->getDataManager()->updatePunishment(
                $punishment,
                $onSuccess,
                $onFailure
            );
        });
    }

    /**
     * Check if a punishment exists
     *
     * @param int $id
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function exists(int $id, callable $onSuccess, callable $onFailure = null) : void {
        $this->plugin->getDataManager()->getPunishment($id, function(array $rows) use ($onSuccess) {
            $onSuccess(count($rows) > 0);
        }, $onFailure);
    }

    /**
     * Gets a punishment instance (if the punishment exists)
     *
     * @param int $id
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function get(int $id, callable $onSuccess, callable $onFailure = null) : void {
        $this->plugin->getDataManager()->getPunishment($id, function(array $rows) use ($onSuccess, $onFailure) {
            $row = $rows[0] ?? null;
            if(is_null($row)) {
                if(!is_null($onFailure)) {
                    $onFailure();
                }
                return;
            }
            $p = new Punishment((int) $row['id'], (int) $row['duration'], $row['description']);;
            $onSuccess($p);
        }, $onFailure);
    }

    /**
     * Gets a list of all punishments
     *
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @return void
     */
    public function getAll(callable $onSuccess, callable $onFailure = null) : void {
        $this->plugin->getDataManager()->getAllPunishments(function (array $rows) use ($onSuccess) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = new Punishment((int) $row['id'], (int) $row['duration'], $row['description']);
            }
            $onSuccess($data);
        }, $onFailure);
    }

}
