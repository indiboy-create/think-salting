<?php

use Youvision\ThinkSalting\Salting;

if (!function_exists('make_salt')) {
    function make_salt($length = null)
    {
        return Salting::makeSalt($length === null ? null : (int) $length);
    }
}

if (!function_exists('admin_password')) {
    function admin_password($password, $salt, $salt2)
    {
        return Salting::encode((string) $password, (string) $salt, (string) $salt2);
    }
}

if (!function_exists('admin_password_verify')) {
    function admin_password_verify($password, $salt, $salt2, $hash)
    {
        return Salting::verify((string) $password, (string) $salt, (string) $salt2, (string) $hash);
    }
}

if (!function_exists('AdminPassword')) {
    function AdminPassword($password, $salt, $salt2)
    {
        return admin_password($password, $salt, $salt2);
    }
}
