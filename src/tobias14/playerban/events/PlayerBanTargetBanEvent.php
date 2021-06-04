<?php
declare(strict_types=1);

namespace tobias14\playerban\events;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use tobias14\playerban\ban\Ban;

class PlayerBanTargetBanEvent extends Event implements Cancellable {

    /** @var Ban $ban */
    protected $ban;

    /**
     * PlayerBanTargetBanEvent constructor.
     *
     * @param Ban $ban
     */
    public function __construct(Ban $ban) {
        $this->ban = $ban;
    }

    /**
     * @return Ban
     */
    public function getBan() : Ban {
        return $this->ban;
    }

    /**
     * @param Ban $ban
     */
    public function setBan(Ban $ban) : void {
        $this->ban = $ban;
    }

}
