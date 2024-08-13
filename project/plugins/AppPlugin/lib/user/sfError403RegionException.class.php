<?php

class sfError403RegionException extends sfError403Exception {

    public function __construct() {
        $_ENV["Forward403Region"] = Organisme::getInstance()->getCurrentRegion();
        if (Organisme::getInstance()->getCurrentRegion()) {
            return parent::__construct("L'opérateur n'est pas habilité pour l'un de vos produits ".Organisme::getInstance()->getCurrentRegion());
        }
        return parent::__construct("Problème d'accès région");
    }

}
