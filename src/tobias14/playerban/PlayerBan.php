<?php

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use tobias14\playerban\commands\PunishmentsCommand;
use tobias14\playerban\database\Database;

/**
 * This class represents the PlayerBan plugin
 *
 * Class PlayerBan
 * @package tobias14\playerban
 */
class PlayerBan extends PluginBase {

    /** @var self $instance */
    private static $instance;
    /** @var BaseLang $baseLang */
    private $baseLang;

    /**
     * Message management
     *
     * @return BaseLang
     */
    public function getLang() : BaseLang {
        return $this->baseLang;
    }

    /**
     * Checks if a punishment with id xy exists
     *
     * @param int $id
     * @return bool
     */
    public function punishmentExists(int $id) : bool {
        $database = Database::connect();
        $res = $database->query("SELECT * FROM punishments WHERE id='{$id}'")->num_rows === 1;
        $database->close();
        return $res;
    }

    /**
     * Returns a list of all punishments (max. 25)
     *
     * @return array
     */
    public function getAllPunishments() : array {
        $database = Database::connect();
        $result = $database->query("SELECT * FROM punishments LIMIT 25");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $database->close();
        return $data;
    }

    /**
     * Class instance
     *
     * @return self
     */
    public static function getInstance() : self {
        return self::$instance;
    }

    public function onLoad() {
        self::$instance = $this;

        $this->saveDefaultConfig();

        $this->saveResource("eng.ini");
        $this->baseLang = new BaseLang("eng", $this->getDataFolder());

        if(false === Database::checkConnection()) {
            $this->getLogger()->error($this->getLang()->translateString("db.connection.failed"));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        Database::init();
    }

    public function onEnable() {
        $command_map = $this->getServer()->getCommandMap();
        $commands = ["ban", "unban", "pardon", "ban-ip", "unban-ip"];
        foreach ($commands as $cmd) {
            if(!is_null($command = $command_map->getCommand($cmd))) {
                $command_map->unregister($command);
            }
        }
        $command_map->registerAll("PlayerBan", [
           new PunishmentsCommand($this)
        ]);
    }

}
