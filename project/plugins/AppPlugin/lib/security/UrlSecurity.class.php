<?php

class UrlSecurity
{
    public static function generateAuthKey($id, $discriminant = null)
    {
        return substr(hash_hmac('sha512', $id.$discriminant, sfConfig::get('app_secret')), 0, 10);
    }

    public static function verifyAuthKey($askedAuthKey, $id, $discriminant = null)
    {

        return self::generateAuthKey($id, $discriminant) == substr($askedAuthKey, 0, 10);
    }
}