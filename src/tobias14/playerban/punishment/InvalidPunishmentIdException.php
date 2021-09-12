<?php
declare(strict_types=1);

namespace tobias14\playerban\punishment;

use Exception;

class InvalidPunishmentIdException extends Exception {

    public function __construct(string $message = '') {
        parent::__construct('PunishmentManager: ' . $message);
    }

}
