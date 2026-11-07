<?php

namespace Youvision\ThinkSalting;

class Salting
{
    public static function makeSalt(?int $length = null): string
    {
        $charset = (string) self::getConfig('charset', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{};:,.<>?/~|');
        $length  = $length ?? (int) self::getConfig('length', 8);

        if ($length < 1) {
            throw new \InvalidArgumentException('盐长度必须大于 0');
        }

        $chars = preg_split('//u', $charset, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false || $chars === []) {
            $chars = str_split($charset);
        }
        if ($chars === []) {
            throw new \InvalidArgumentException('盐字符池不能为空');
        }

        $max  = count($chars) - 1;
        $salt = '';

        for ($i = 0; $i < $length; $i++) {
            $salt .= $chars[random_int(0, $max)];
        }

        return $salt;
    }

    public static function makePair(?int $length = null, ?int $length2 = null): array
    {
        return [
            'salt'  => self::makeSalt($length),
            'salt2' => self::makeSalt($length2 ?? (int) self::getConfig('length2', 10)),
        ];
    }

    public static function encode(string $password, string $salt, string $salt2): string
    {
        $prefix = (string) self::getConfig('prefix', 'Y!o^U');
        $suffix = (string) self::getConfig('suffix', 'V,is*Io(N');

        return md5($salt2 . $prefix . md5(md5($password) . $salt) . $suffix . $salt);
    }

    public static function verify(string $password, string $salt, string $salt2, string $hash): bool
    {
        return hash_equals($hash, self::encode($password, $salt, $salt2));
    }


    public static function getConfig(string $name, mixed $default = null): mixed
    {
        if (!class_exists(\think\facade\Config::class)) {
            return $default;
        }

        try {
            $value = \think\facade\Config::get('salting.' . $name);
        } catch (\Throwable $e) {
            return $default;
        }

        return $value === null ? $default : $value;
    }
}