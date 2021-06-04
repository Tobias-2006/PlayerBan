<?php
declare(strict_types=1);

namespace tobias14\playerban\forms;

use jojoe77777\FormAPI\ModalForm;
use tobias14\playerban\PlayerBan;

abstract class ModalBaseForm extends ModalForm {

    /**
     * ModalBaseForm constructor.
     *
     * @param callable|null $callable
     */
    public function __construct(?callable $callable) {
        parent::__construct($callable);
    }

    /**
     * Massage Management
     *
     * @param string $str
     * @param int[]|float[]|string[] $params
     * @return string
     */
    protected function translate(string $str, array $params = []) : string {
        return PlayerBan::getInstance()->getLanguage()->translateString($str, $params);
    }

}
