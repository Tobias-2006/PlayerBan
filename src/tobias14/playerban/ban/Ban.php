<?php

namespace tobias14\playerban\ban;

use tobias14\playerban\PlayerBan;

/**
 * This class represents the ban instance
 *
 * Class Ban
 * @package tobias14\playerban\ban
 */
class Ban {

    /** @var int $creation_time */
    protected $creation_time;

    /** @var string $target */
    public $target;
    /** @var string $moderator */
    public $moderator;
    /** @var int $expiry_time */
    public $expiry_time;
    /** @var int $pun_id */
    public $pun_id;

    public function __construct() {
        $this->creation_time = time();
    }

    /**
     * Saving to the database
     *
     * @return null|bool
     */
    public function save() : ?bool {
        return PlayerBan::getInstance()->getDataManager()->saveBan(
            $this->target,
            $this->moderator,
            $this->expiry_time,
            $this->pun_id,
            $this->creation_time
        );
    }

}
