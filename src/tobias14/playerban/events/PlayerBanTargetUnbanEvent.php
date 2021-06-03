<?php
declare(strict_types=1);

namespace tobias14\playerban\events;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;

class PlayerBanTargetUnbanEvent extends Event implements Cancellable {

    /** @var string $target */
    protected $target;

    /**
     * PlayerBanTargetUnbanEvent constructor.
     *
     * @param string $target
     */
    public function __construct(string $target) {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget() : string {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target) : void {
        $this->target = $target;
    }

}
