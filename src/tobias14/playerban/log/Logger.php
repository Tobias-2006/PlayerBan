<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

use tobias14\playerban\database\DataManager;

class Logger {

    /** @var DataManager $dataMgr */
    private $dataMgr;

    /** @var self $instance */
    private static $instance;

    public const LOG_TYPE_CREATION = 0;
    public const LOG_TYPE_DELETION = 1;
    public const LOG_TYPE_ADAPTATION = 2;

    /**
     * Logger constructor.
     */
    public function __construct(DataManager $dataMgr) {
        $this->dataMgr = $dataMgr;
    }

    /**
     * @return self
     */
    public static function getLogger() : self {
        return self::$instance;
    }

    /**
     * @return void
     */
    public function register() : void {
        if(!isset(self::$instance))
            self::$instance = $this;
    }

    /**
     * Creates a new log and saves it into the database
     *
     * @param Log $log
     * @return bool
     */
    public function log(Log $log) : bool {
        if(!$log->hasValidType())
            return false;
        return $this->dataMgr->saveLog($log) ?? false;
    }

}
