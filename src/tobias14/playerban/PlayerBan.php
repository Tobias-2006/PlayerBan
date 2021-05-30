<?php
declare(strict_types=1);

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use tobias14\playerban\commands\BanCommand;
use tobias14\playerban\commands\BanHistoryCommand;
use tobias14\playerban\commands\BanListCommand;
use tobias14\playerban\commands\BanLogsCommand;
use tobias14\playerban\commands\PunishmentListCommand;
use tobias14\playerban\commands\PunishmentsCommand;
use tobias14\playerban\commands\UnbanCommand;
use tobias14\playerban\database\DataManager;
use tobias14\playerban\database\MysqlManager;
use tobias14\playerban\database\SqliteManager;

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
     * Database management
     *
     * @return DataManager
     */
    public function getDataManager() : DataManager {
        return $this->dataMgr;
    }

    /**
     * Class instance
     *
     * @return self
     */
    public static function getInstance() : self {
        return self::$instance;
    }

    /**
     * Formats a timestamp into a string
     *
     * @param int $timestamp
     * @return string
     */
    public function formatTime(int $timestamp) : string {
        return date("d.m.Y | H:i", $timestamp);
    }

    /**
     * Checks if a username is valid
     *
     * @param string $name
     * @return bool
     */
    public function isValidUserName(string $name) : bool {
        $lname = strtolower($name);
        $len = strlen($name);
        return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
    }

    /**
     * Checks if an ip address is valid
     *
     * @param string $address
     * @return bool
     */
    public function isValidAddress(string $address) : bool {
        return preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $address) === 1;
    }

    /**
     * Returns a list of the mysql connection details
     *
     * @return string[]
     */
    private function getMySQLSettings() : array {
        return [
            'Host' => $this->getConfig()->get("host", "127.0.0.1"),
            'Username' => $this->getConfig()->get("username", "root"),
            'Password' => $this->getConfig()->get("passwd", "password"),
            'Database' => $this->getConfig()->get("dbname", "playerban"),
            'Port' => $this->getConfig()->get("port", 3306)
        ];
    }

    /**
     * Sets the chosen DataManager
     *
     * @return void
     */
    private function setDataMgr() : void {
        $datamanager = $this->getConfig()->get("datamanager", "sqlite");
        switch ($datamanager) {
            case "mysql":
            case "MySql":
            case "MySQL":
                $this->dataMgr = new MysqlManager($this, $this->getMySQLSettings());
                break;
            case "sqlite":
            case "sqlite3":
            default:
                $this->dataMgr = new SqliteManager($this, []);
        }
    }

    public function onLoad() {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $lang = $this->getConfig()->get("language", BaseLang::FALLBACK_LANGUAGE);
        $this->baseLang = new BaseLang($lang, $this->getFile() . 'resources/');
    }

    public function onEnable() {
        $this->setDataMgr();
        $commandMap = $this->getServer()->getCommandMap();
        $commands = ["ban", "unban", "pardon", "ban-ip", "unban-ip", "banlist"];
        foreach ($commands as $cmd) {
            if(!is_null($command = $commandMap->getCommand($cmd))) {
                $commandMap->unregister($command);
            }
        }
        $commandMap->registerAll("PlayerBan", [
            new PunishmentsCommand($this),
            new PunishmentListCommand($this),
            new BanLogsCommand($this),
            new BanCommand($this),
            new UnbanCommand($this),
            new BanListCommand($this),
            new BanHistoryCommand($this)
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function onDisable() {
        $this->dataMgr->close();
    }

}
