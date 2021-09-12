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

        $banManager->isBanned($name, function(bool $banned) use ($banManager, $name, $address, $event) {
            $target = null;
            if($banned) {
               $target = $name;
            }
            $banManager->isBanned($address, function(bool $banned) use ($banManager, $target, $address, $event) {
               if($banned) {
                   $target = $address;
               }
               if(is_null($target)) {
                   return;
               }
               $banManager->get($target, function(Ban $ban) use ($event) {
                   $expiry = $this->plugin->formatTime($ban->expiryTime);
                   $event->setKickMessage($this->plugin->customTranslation($this->kickMessage, ['{expiry}' => $expiry, '{moderator}' => $ban->moderator, '{new_line}' => "\n"]));
                   $event->setCancelled();
               }, function () use ($event) {
                   $ban = new Ban('undefined', 'undefined', -1, -1);
                   $event->setKickMessage($this->plugin->customTranslation($this->kickMessage, ['{expiry}' => 'undefined', '{moderator}' => $ban->moderator, '{new_line}' => "\n"]));
                   $event->setCancelled();
               });
            });
        });
        $this->plugin->getDataManager()->block();
    }

}
