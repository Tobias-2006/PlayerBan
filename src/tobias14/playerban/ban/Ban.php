<?php

namespace tobias14\playerban\ban;


use tobias14\playerban\PlayerBan;

/**
 * This class represents the ban instance
 *
 * Class Ban
 * @package tobias14\playerban\ban
 */
abstract class Ban {

    /** @var int $creation_time */
    protected $creation_time;

    /** @var string $target */
    public $target;
    /** @var string $moderator */
    public $moderator;
    /** @var int $duration */
    public $duration;

    /**
     * Saving to the database
     *
     * @return null|bool
     */
    public function save() : ?bool {
        return PlayerBan::getInstance()->getDataManager()->saveBan(
            $this->target,
            $this->moderator,
            $this->duration,
            $this->creation_time
        );
    }

}
