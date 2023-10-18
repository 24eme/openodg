<?php

class sfError403RegionException extends sfError403Exception {

    public function __construct(Compte $user) {
        if ($user->region) {
            return parent::__construct("L'opérateur n'est pas habilité pour l'un de vos produits ".$user->region);
        }
        return parent::__construct("Problème d'accès région");
    }

}
