<?php

namespace Atto\AttoHttp\traits;

trait staticCurrent
{
    //默认实例
    //public static $current = null;

    //生成默认实例
    public static function current()
    {
        $args = func_get_args();
        if (is_null(self::$current)) self::$current = new self(...$args);
        return self::$current;
    }
}