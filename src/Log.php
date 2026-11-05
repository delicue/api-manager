<?php 

namespace App;

class Log {
    public static function info(string $message) {
        error_log(message: "[INFO] $message");
    }

    public static function error(string $message) {
        error_log(message: "[ERROR] $message");
    }
}
