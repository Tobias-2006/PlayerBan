<?php
declare(strict_types=1);

namespace tobias14\playerban\utils;

interface Queries
{

    /**
     * MySQL query defined at
     * resources/mysql.sql:4
     */
    public const PLAYERBAN_INIT_BANS = 'playerban.init.bans';

    /**
     * MySQL query defined at
     * resources/mysql.sql:15
     */
    public const PLAYERBAN_INIT_PUNISHMENTS = 'playerban.init.punishments';

    /**
     * MySQL query defined at
     * resources/mysql.sql:23
     */
    public const PLAYERBAN_INIT_LOGS = 'playerban.init.logs';

    /**
     * MySQL query defined at
     * resources/mysql.sql:34
     */
    public const PLAYERBAN_LOG_SAVE = 'playerban.log.save';

    /**
     * MySQL query defined at
     * resources/mysql.sql:54
     */
    public const PLAYERBAN_LOG_DELETE = 'playerban.log.delete';

    /**
     * MySQL query defined at
     * resources/mysql.sql:59
     */
    public const PLAYERBAN_LOG_GET_PAGE = 'playerban.log.get.page';

    /**
     * MySQL query defined at
     * resources/mysql.sql:64
     */
    public const PLAYERBAN_LOG_GET_LOGCOUNT = 'playerban.log.get.logcount';

    /**
     * MySQL query defined at
     * resources/mysql.sql:69
     */
    public const PLAYERBAN_PUNISHMENT_GET = 'playerban.punishment.get';

    /**
     * MySQL query defined at
     * resources/mysql.sql:73
     */
    public const PLAYERBAN_PUNISHMENT_GET_ALL = 'playerban.punishment.get.all';

    /**
     * MySQL query defined at
     * resources/mysql.sql:76
     */
    public const PLAYERBAN_PUNISHMENT_SAVE = 'playerban.punishment.save';

    /**
     * MySQL query defined at
     * resources/mysql.sql:90
     */
    public const PLAYERBAN_PUNISHMENT_DELETE = 'playerban.punishment.delete';

    /**
     * MySQL query defined at
     * resources/mysql.sql:94
     */
    public const PLAYERBAN_PUNISHMENT_UPDATE = 'playerban.punishment.update';

    /**
     * MySQL query defined at
     * resources/mysql.sql:102
     */
    public const PLAYERBAN_BAN_GET = 'playerban.ban.get';

    /**
     * MySQL query defined at
     * resources/mysql.sql:107
     */
    public const PLAYERBAN_BAN_SAVE = 'playerban.ban.save';

    /**
     * MySQL query defined at
     * resources/mysql.sql:127
     */
    public const PLAYERBAN_BAN_REMOVE = 'playerban.ban.remove';

    /**
     * MySQL query defined at
     * resources/mysql.sql:132
     */
    public const PLAYERBAN_BAN_GET_BANHISTORY = 'playerban.ban.get.banhistory';

    /**
     * MySQL query defined at
     * resources/mysql.sql:136
     */
    public const PLAYERBAN_BAN_GET_CURRENTBANS = 'playerban.ban.get.currentbans';

    /**
     * MySQL query defined at
     * resources/mysql.sql:142
     */
    public const PLAYERBAN_BAN_GET_CURRENTBANS_COUNT = 'playerban.ban.get.currentbans.count';

}
