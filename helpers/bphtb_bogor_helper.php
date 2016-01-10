<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -- author: irul -- */

// to_decimal
if ( ! function_exists('to_decimal'))
{
    function to_decimal($str_val, $ret_val = NULL)
    {
        $val = $str_val;
        $val = str_replace(".", "", $val);
        $val = str_replace(",", ".", $val);
        return $val != '' ? $val : (!empty($ret_val) ? $ret_val : 0);
    }
}
