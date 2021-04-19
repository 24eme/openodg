<?php

class UrlSecurity
{
    public static function generateAuthKey($id, $discriminant = null)
    {
        return substr(hash_hmac('sha512', $id.$discriminant, sfConfig::get('app_secret')), 0, 10);
    }

    public static function verifyAuthKey($askedAuthKey, $id, $discriminant = null)
    {
        // Pour assurer une compatibilité temporaire avec des mails qui ont été généré en md5 pour l'ava en mars 2021. (peut être supprimé dés la prochaine récolte)
        if(substr(hash_hmac('md5', $id.$discriminant, sfConfig::get('app_secret')), 0, 10) == substr($askedAuthKey, 0, 10)) {

            return true;
        }

        return self::generateAuthKey($id, $discriminant) == substr($askedAuthKey, 0, 10);
    }
}