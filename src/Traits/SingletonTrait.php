<?php

namespace J3dyy\CsvOperationParser\Traits;

trait SingletonTrait
{
    private static self $_instance;

    private function __construct()
    {

    }

    private function __clone()
    {
        // Don't do anything, we don't want to be cloned
    }


    public static function instance(): self
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }


        return self::$_instance;
    }
}
