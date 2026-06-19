<?php

if (! function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1)
    {
        $regex = '~'.str_replace('~', '\~', $pattern).'~u';

        return preg_split($regex, $string, $limit ?: -1);
    }
}
