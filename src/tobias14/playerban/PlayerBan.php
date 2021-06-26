<?php
declare(strict_types=1);

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use tobias14\playerban\ban\BanManager;
use tobias14\playerban\commands\{
    BanCommand,
    BanHistoryCommand,
    BanListCommand,
    BanLogsCommand,
    PunishmentListCommand,
    PunishmentsCommand,
    UnbanCommand
};
use tobias14\playerban\database\{DataManager, MysqlManager, SqliteManager};
use tobias14\playerban\log\Logger;
use tobias14\playerban\punishment\PunishmentManager;

class PlayerBan extends PluginBase {

    /** @var self $instance */
    private static $instance;
    /** @var BaseLang $baseLang */
    private $baseLang;
    /** @var DataManager $dataMgr */
    private $dataMgr;
    /** @var BanManager $banMgr */
    private $banMgr;
    /** @var PunishmentManager $punishmentMgr */
    protected $punishmentMgr;

    /**
     * Class instance
     *
     * @return self
     */
    public static function getInstance() : self {
        return self::$instance;
    }

    /**
     * Message management
     *
     * @return BaseLang
     */
    public function getLanguage() : BaseLang {
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
     * Ban management
     *
     * @return BanManager
     */
    public function getBanManager() : BanManager {
        return $this->banMgr;
    }

    /**
     * Punishment management
     *
     * @return PunishmentManager
     */
    public function getPunishmentManager() : PunishmentManager {
        return $this->punishmentMgr;
    }

    /**
     * For messages of the configuration file
     *
     * @param string $string
     * @param string[] $params
     * @return string
     */
    public function customTranslation(string $string, array $params) : string {
        $text = TextFormat::colorize($string);
        foreach ($params as $key => $val) {
            $text = str_replace($key, $val, $text);
        }
        return $text;
    }

    /**
     * Formats a timestamp into a string
     *
     * @param int $timestamp
     * @return string
     */
    public function formatTime(int $timestamp) : string {
        return date($this->getDateFormat(), $timestamp);
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
     * Returns the specified date format
     *
     * @return string
     */
    private function getDateFormat() : string {
        $dateFormat = $this->getConfig()->get('date-format', 'dd.mm.yyyy');
        switch ($dateFormat) {
            case 'mm.dd.yyyy':
                $format = 'm.d.Y | H:i';
                break;
            case 'yyyy.mm.dd':
                $format = 'Y.m.d | H:i';
                break;
            case 'dd.mm.yyyy':
            default:
                $format = 'd.m.Y | H:i';
        }
        return $format;
    }

    /**
     * Sets the chosen DataManager
     *
     * @return void
     */
    private function setDataManager() : void {
        $datamanager = (string) $this->getConfig()->get("datamanager", "sqlite");
        switch (strtolower($datamanager)) {
            case "mysql":
                try {
                    $this->dataMgr = new MysqlManager($this, $this->getConfig()->get("mysql-connection"));
                } catch (\Exception $e) {
                    $this->getLogger()->critical("MySQL datamanager crashed! Using SQLite instead of MySQL.");
                    $this->dataMgr = new SqliteManager($this, []);
                }
                break;
            case "sqlite":
            case "sqlite3":
            default:
                $this->dataMgr = new SqliteManager($this, []);
        }
    }

    public function onLoad() {
        self::$instance = $this;
        $this->reloadConfig();
        $lang = $this->getConfig()->get("language", BaseLang::FALLBACK_LANGUAGE);
        $this->baseLang = new BaseLang($lang, $this->getFile() . 'resources/');
    }

    public function onEnable() {
        $this->setDataManager();
        $this->banMgr = new BanManager($this);
        $this->punishmentMgr = new PunishmentManager($this);
        $logger = new Logger($this->getDataManager());
        $logger->register();
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
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable() {
        if(isset($this->dataMgr))
            $this->dataMgr->close();
    }

}
