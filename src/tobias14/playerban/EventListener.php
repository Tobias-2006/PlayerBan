<?php
declare(strict_types=1);

namespace tobias14\playerban;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;

class EventListener implements Listener {

    public function onPreLogin(PlayerPreLoginEvent $event) : void {
        $name = $event->getPlayer()->getName();
        $address = $event->getPlayer()->getAddress();
        $target = null;

        if($this->isBanned($name))
            $target = $name;
        elseif($this->isBanned($address))
            $target = $address;
        if(is_null($target))
            return;

        $ban = PlayerBan::getInstance()->getDataManager()->getBanByName($target) ?? ['expiry_time' => 'undefined', 'moderator' => 'undefined'];
        $event->getPlayer()->kick(PlayerBan::getInstance()->getKickMessage($ban), false);
    }

    /**
     * @param string $target
     * @return bool
     */
    private function isBanned(string $target) : bool {
        return PlayerBan::getInstance()->getDataManager()->isBanned($target) === true;
    }

}
