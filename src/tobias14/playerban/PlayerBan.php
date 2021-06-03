<?php
declare(strict_types=1);

namespace tobias14\playerban;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\PluginBase;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\ban\BanManager;
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
     * The message that appears when a banned player wants to enter the server
     *
     * @param Ban $ban
     * @return string
     */
    public function getKickMessage(Ban $ban) : string {
        $expiry = $ban->expiryTime !== -1 ? $this->formatTime($ban->expiryTime) : (string) $ban->expiryTime;
        $data = ['{expiry}' => $expiry, '{moderator}' => $ban->moderator, '{new_line}' => "\n"];
        $message = $this->getConfig()->get('kick-message', '§cYou are banned!{new_line}{new_line}§4Expiry: §f{expiry}');
        foreach ($data as $search => $replace) {
            $message = str_replace($search, $replace, $message);
        }
        return $message;
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
    private function setDataManager() : void {
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
        $this->setDataManager();
        $this->banMgr = new BanManager($this);
        $this->punishmentMgr = new PunishmentManager($this);
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
