<?php
declare(strict_types=1);

namespace tobias14\playerban\ban;

use tobias14\playerban\PlayerBan;

class Ban {

    /** @var string $target */
    protected $target;
    /** @var string $moderator */
    protected $moderator;
    /** @var int $expiryTime */
    protected $expiryTime;
    /** @var int $punId */
    protected $punId;
    /** @var int $creationTime */
    protected $creationTime;

    /**
     * Ban constructor.
     *
     * @param string $target
     * @param string $moderator
     * @param int $expiryTime
     * @param int $punId
     */
    public function __construct(string $target, string $moderator, int $expiryTime, int $punId) {
        $this->target = $target;
        $this->moderator = $moderator;
        $this->expiryTime = $expiryTime;
        $this->punId = $punId;
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
