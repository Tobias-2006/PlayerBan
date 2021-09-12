<?php
declare(strict_types=1);

namespace tobias14\playerban\log;

use Exception;

class InvalidLogTypeException extends Exception {

    public function __construct(string $message = '') {
        parent::__construct('Logger: ' . $message);
    }

}
