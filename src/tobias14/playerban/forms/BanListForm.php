<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use pocketmine\Player;
use tobias14\playerban\ban\Ban;
use tobias14\playerban\forms\subforms\BanListSubForm;
use tobias14\playerban\PlayerBan;
use tobias14\playerban\punishment\Punishment;

class BanListForm extends SimpleBaseForm {

    /**
     * BanListForm constructor.
     *
     * @param Ban[] $bans
     * @param int $maxCurrentBansPage
     * @param int $page
     */
    public function __construct(array $bans, int $maxCurrentBansPage, int $page = 0) {
        parent::__construct($this->onCall($bans, $page));
        $this->setTitle($this->translate("banlist.form.title"));
        foreach ($bans as $ban)
            $this->addButton($this->translate("banlist.form.button", [$this->formatTime($ban->creationTime), $ban->target]));
        if($maxCurrentBansPage > ($page + 1))
            $this->addButton($this->translate("button.nextPage"));
    }

    /**
     * @param Ban[] $bans
     * @param int $page
     * @return callable
     */
    protected function onCall(array $bans, int $page) : callable {
        return function (Player $player, $data) use ($bans, $page) {
            if(is_null($data)) return;
            if(count($bans) === $data) {
                self::getCurrentBansForPage(function($logs) use ($player, $page) {
                    self::getMaxCurrentBansPage(function($maxLogPage) use ($player, $page, $logs) {
                        $player->sendForm(new BanListForm($logs, $maxLogPage, $page + 1));
                    });
                }, null, $page + 1);
                return;
            }
            $ban = $bans[$data];
            $this->getPunishmentMgr()->get($ban->punId, function(Punishment $punishment) use($player, $ban, $page) {
                $player->sendForm(new BanListSubForm($ban, $page, $punishment));
            }, function() use($player, $ban, $page) {
                $player->sendForm(new BanListSubForm($ban, $page));
            });
        };
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $page
     * @param int $limit
     */
    public static function getCurrentBansForPage(callable $onSuccess, callable $onFailure = null, int $page = 0, int $limit = 6) : void {
        PlayerBan::getInstance()->getDataManager()->getCurrentBansForPage(function (array $rows) use ($onSuccess) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = new Ban($row['target'], $row['moderator'], (int) $row['expiry_time'], (int) $row['pun_id'], (int) $row['id'], (int) $row['creation_time']);
            }
            $onSuccess($data);
        }, $onFailure, $page, $limit);
    }

    /**
     * @param callable $onSuccess
     * @param callable|null $onFailure
     * @param int $limit
     */
    public static function getMaxCurrentBansPage(callable $onSuccess, callable $onFailure = null, int $limit = 6) : void {
        PlayerBan::getInstance()->getDataManager()->getCurrentBansCount(function(array $rows) use ($onSuccess, $limit) {
            $rowCount = (int) $rows[0]['COUNT(*)'];
            $sites = $rowCount / $limit;
            if(($rowCount % $limit) != 0)
                $sites += 1;
            $onSuccess((int) floor($sites));
        }, $onFailure, $limit);
    }

}
