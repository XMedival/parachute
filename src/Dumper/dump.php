<?php

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        $isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');

        if (!$isCli) {
            echo "<pre style=\"background:darkgray;color:crimson;white-space:pre-wrap;word-wrap:break-word;\">";
        }

        foreach ($vars as $v) {
            // var_export is readable for arrays/scalars
            echo var_export($v, true), "\n\n";
        }

        if (!$isCli) {
            echo "</pre>";
        }

        die();
        exit(1);
    }
}

if (!function_exists('d')) {
    function d(...$vars): void
    {
        $isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');

        if (!$isCli) {
            echo "<pre style=\"background:darkgray;color:crimson;white-space:pre-wrap;word-wrap:break-word;\">";
        }

        foreach ($vars as $v) {
            // var_export is readable for arrays/scalars
            echo var_export($v, true), "\n\n";
        }

        if (!$isCli) {
            echo "</pre>";
        }
    }
}
