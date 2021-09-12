<?php
declare(strict_types=1);

namespace tobias14\playerban\ban;

use Exception;

class InvalidTargetException extends Exception {

    public function __construct(string $message = '') {
        parent::__construct('BanManager: ' . $message);
    }

}
