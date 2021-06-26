<?php
declare(strict_types=1);

namespace tobias14\playerban;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use tobias14\playerban\ban\Ban;

class EventListener implements Listener {

    /** @var PlayerBan $plugin */
    protected $plugin;
    /** @var string $kickMessage */
    protected $kickMessage;

    /**
     * EventListener constructor.
     *
     * @param PlayerBan $plugin
     */
    public function __construct(PlayerBan $plugin) {
       $this->plugin = $plugin;
       $this->kickMessage = $this->plugin->getConfig()->get('kick-message', 'You are banned!');
    }

    public function onPreLogin(PlayerPreLoginEvent $event) : void {
        $name = $event->getPlayer()->getName();
        $address = $event->getPlayer()->getAddress();
        $banManager = $this->plugin->getBanManager();
        $target = null;

        if($banManager->isBanned($name))
            $target = $name;
        elseif($banManager->isBanned($address))
            $target = $address;
        if(is_null($target))
            return;

        $ban = $banManager->get($target) ?? new Ban('undefined', 'undefined', -1, -1);
        $expiry = $ban->expiryTime !== -1 ? $this->plugin->formatTime($ban->expiryTime) : 'undefined';
        $event->setKickMessage($this->plugin->customTranslation($this->kickMessage, ['{expiry}' => $expiry, '{moderator}' => $ban->moderator, '{new_line}' => "\n"]));
        $event->setCancelled();
    }

}
