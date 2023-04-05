<?php

class DeclarationExportCsv
{
    public static function getProduitKeysCsv($produitconfig, $withLibelle = false){
        if (!$produitconfig) {
            $res = ';;;;;;';
            if ($withLibelle) {
                $res .= ';;;;;;;';
            }
            return $res;
        }
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
        if ($produitconfig->hasLieu()) {
            $res .= $produitconfig->getLieu()->getKey().";";
            if ($withLibelle) {
                $res .= $produitconfig->getLieu()->getLibelle().";";
            }
        }else{
            $res .= ';';
            if ($withLibelle) {
                $res .= ';';
            }
        }
        if ($produitconfig->hasCouleur()) {
            $res .= $produitconfig->getCouleur()->getKey().";";
            if ($withLibelle) {
                $res .= $produitconfig->getCouleur()->getLibelle().";";
            }
        }else{
            $res .= ';';
            if ($withLibelle) {
                $res .= ';';
            }
        }
        if ($produitconfig->hasCepage()) {
            $res .= $produitconfig->getCepage()->getKey();
            if ($withLibelle) {
                $res .= ";".$produitconfig->getCepage()->getLibelle();
            }
        }else{
            $res .= ';';
            if ($withLibelle) {
                $res .= ';';
            }
        }
        return str_replace("DEFAUT", "", $res);
    }
}