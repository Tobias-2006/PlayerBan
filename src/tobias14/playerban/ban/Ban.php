<?php
declare(strict_types=1);

namespace tobias14\playerban\ban;

class Ban {

    /** @var string $target */
    public $target;
    /** @var string $moderator */
    public $moderator;
    /** @var int $expiryTime */
    public $expiryTime;
    /** @var int $punId */
    public $punId;
    /** @var int $id */
    public $id;
    /** @var int $creationTime */
    public $creationTime;

    /**
     * Ban constructor.
     *
     * @param string $target
     * @param string $moderator
     * @param int $expiryTime
     * @param int $punId
     * @param int $id
     * @param int $creationTime
     */
    public function __construct(string $target, string $moderator, int $expiryTime, int $punId, int $id = -1, int $creationTime = -1) {
        $this->target = $target;
        $this->moderator = $moderator;
        $this->expiryTime = $expiryTime;
        $this->punId = $punId;
        $this->id = $id;
        $this->creationTime = $creationTime;
    }

}
