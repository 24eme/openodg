<?php

class ParcellaireEtapes {

    const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_PROPRIETE = 'propriete';
    const ETAPE_PARCELLES = 'parcelles';
    const ETAPE_ACHETEURS = 'acheteurs';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_EXPLOITATION,
        self::ETAPE_PROPRIETE,
        self::ETAPE_PARCELLES,
        self::ETAPE_ACHETEURS,
        self::ETAPE_VALIDATION,
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireEtapes();
        }
        return self::$_instance;
    }

    public function __construct() {
        
    }

    public function getEtapes() {
        return self::$etapes;
    }

    public function getFirst() {
        $etapes = $this->getEtapes();
        $first = null;
        foreach ($etapes as $etape) {
            $first = $etape;
            break;
        }
        return $first;
    }

    public function getNext($etape) {
        if (!$etape) {
            return $this->getFirst();
        }
        $etapes = $this->getEtapes();
        if (!in_array($etape, $etapes)) {
            throw new sfException('Etape inconnu');
        }
        $find = false;
        $next = self::ETAPE_VALIDATION;
        foreach ($etapes as $e) {
            if ($find) {
                $next = $e;
                break;
            }
            if ($etape == $e) {
                $find = true;
            }
        }
        return $next;
    }

    public function isGt($etapeToTest, $etape, $strict = false) {
        $etapes = $this->getEtapes();
        if (!$etapeToTest) {
            return false;
        }
        if (!in_array($etapeToTest, $etapes)) {
            throw new sfException('"' . $etapeToTest . '" : étape inconnu');
        }
        if (!in_array($etape, $etapes)) {
            throw new sfException('"' . $etape . '" : étape inconnu');
        }
        $key = array_search($etape, $etapes);
        $keyToTest = array_search($etapeToTest, $etapes);
        if ($strict) {
            return ($keyToTest > $key);
        }
        return ($keyToTest >= $key);
    }

    public function isLt($etapeToTest, $etape) {
        return !$this->isGt($etapeToTest, $etape);
    }

}
