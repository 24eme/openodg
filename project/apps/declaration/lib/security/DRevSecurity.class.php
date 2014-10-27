<?php

class DRevSecurity extends DocumentSecurity implements SecurityInterface {

    const EDITION = 'EDITION';

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new DRevSecurity($user, $doc);
    }

}