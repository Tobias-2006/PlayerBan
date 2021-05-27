<?php
declare(strict_types=1);

namespace tobias14\playerban\ban;

use tobias14\playerban\PlayerBan;

class Ban {

    /** @var int $creationTime */
    protected $creationTime;

    /** @var string $target */
    public $target;
    /** @var string $moderator */
    public $moderator;
    /** @var int $expiryTime */
    public $expiryTime;
    /** @var int $punId */
    public $punId;

    public function __construct() {
        $this->creationTime = time();
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
            $this->expiryTime,
            $this->punId,
            $this->creationTime
        );
    }

}
