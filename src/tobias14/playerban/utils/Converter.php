<?php

namespace tobias14\playerban\utils;

// Short forms
const MINUTES = "m", HOURS = "h", DAYS = "d";

class Converter {

    public static function str_to_seconds(string $string) : ?int {
        // FORMAT: 12d,3h,20m
        if(!preg_match("/(^[1-9][0-9]{0,2}[mhd])(,[1-9][0-9]{0,2}[mhd]){0,2}$/", $string)) {
            return null;
        }
        $time = 0;
        $parts = explode(",", $string);
        foreach($parts as $part) {
            $chars = str_split($part, (strlen($part) - 1));
            switch ($chars[1]) {
                case MINUTES:
                    $time += ((int) $chars[0] * 60);
                    break;
                case HOURS:
                    $time += ((int) $chars[0] * 3600);
                    break;
                case DAYS:
                    $time += ((int) $chars[0] * 86400);
                    break;
            }
        }
        return $time;
    }

    public static function seconds_to_str(int $seconds) : string {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor((($seconds % 86400) % 3600) / 60);

        $days = $days > 0 ? $days . "d" : "";
        $hours = $hours > 0 ? $hours . "h" : "";
        $minutes = $minutes > 0 ? $minutes . "m" : "";

        $data = [$days, $hours, $minutes];
        for ($i = 0; $i <= count($data); $i++) {
            if($data[$i] === "") {
                unset($data[$i]);
            }
        }

        return implode(",", $data);
    }

}
