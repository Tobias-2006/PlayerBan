<?php

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use tobias14\playerban\commands\BanCommand;
use tobias14\playerban\commands\BanListCommand;
use tobias14\playerban\commands\BanLogsCommand;
use tobias14\playerban\commands\PunishmentListCommand;
use tobias14\playerban\commands\PunishmentsCommand;
use tobias14\playerban\commands\UnbanCommand;
use tobias14\playerban\database\DataManager;

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
    /** @var DataManager $dataMgr */
    private $dataMgr;

    /**
     * Message management
     *
     * @return BaseLang
     */
    public function getLang() : BaseLang {
        return $this->baseLang;
    }

    /**
     * @return DataManager
     */
    public function getDataManager() : DataManager {
        return $this->dataMgr;
    }

    /**
     * @return array
     */
    public function getDatabaseSettings() : array {
        return [
            'Host' => $this->getConfig()->get("host", "127.0.0.1"),
            'Username' => $this->getConfig()->get("username", "root"),
            'Password' => $this->getConfig()->get("passwd", "password"),
            'Database' => $this->getConfig()->get("dbname", "playerban"),
            'Port' => $this->getConfig()->get("port", 3306)
        ];
    }

    /**
     * Checks if a punishment with id xy exists
     *
     * @param int $id
     * @return null|bool
     */
    public function punishmentExists(int $id) : ?bool {
        return $this->getDataManager()->punishmentExists($id);
    }

    /**
     * Returns a list of all punishments (max. 25)
     *
     * @return null|array
     */
    public function getAllPunishments() : ?array {
        return $this->getDataManager()->getAllPunishments();
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
    }

    public function onEnable() {
        $this->dataMgr = new DataManager($this, $this->getDatabaseSettings());
        $command_map = $this->getServer()->getCommandMap();
        $commands = ["ban", "unban", "pardon", "ban-ip", "unban-ip", "banlist"];
        foreach ($commands as $cmd) {
            if(!is_null($command = $command_map->getCommand($cmd))) {
                $command_map->unregister($command);
            }
        }
        $command_map->registerAll("PlayerBan", [
            new PunishmentsCommand($this),
            new PunishmentListCommand($this),
            new BanLogsCommand($this),
            new BanCommand($this),
            new UnbanCommand($this),
            new BanListCommand($this)
        ]);
    }

}
