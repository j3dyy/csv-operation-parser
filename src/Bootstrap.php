<?php

namespace J3dyy\CsvOperationParser;

class Bootstrap
{
    public static function boot(): void
    {
        self::loadConfiguration();
    }

    private static function loadConfiguration(): void
    {
        Configuration::instance()->loadConfiguration();
    }
}
