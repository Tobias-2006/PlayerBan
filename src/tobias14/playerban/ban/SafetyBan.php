<?php

namespace tobias14\playerban\ban;

class SafetyBan extends Ban {

    public function __construct() {
        $this->creation_time = time();
    }

}
