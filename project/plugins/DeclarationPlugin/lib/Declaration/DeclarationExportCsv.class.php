<?php

class DeclarationExportCsv
{
    public static function getProduitKeysCsv($produitconfig, $withLibelle = false){

        $res  = $produitconfig->getCertification()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getCertification()->getLibelle().";";
        }
        $res .= $produitconfig->getGenre()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getGenre()->getLibelle().";";
        }
        $res .= $produitconfig->getAppellation()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getAppellation()->getLibelle().";";
        }
        $res .= $produitconfig->getMention()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getMention()->getLibelle().";";
        }
        $res .= $produitconfig->getLieu()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getLieu()->getLibelle().";";
        }
        $res .= $produitconfig->getCouleur()->getKey().";";
        if ($withLibelle) {
            $res .= $produitconfig->getCouleur()->getLibelle().";";
        }
        $res .= $produitconfig->getCepage()->getKey();
        if ($withLibelle) {
            $res .= $produitconfig->getCepage()->getLibelle().";";
        }

        return str_replace("DEFAUT", "", $res);
    }
}