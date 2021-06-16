<?php

class DeclarationExportCsv
{
    public static function getProduitKeysCsv($produitconfig){

        return str_replace("DEFAUT", "", $produitconfig->getCertification()->getKey().";".
        $produitconfig->getGenre()->getKey().";".
        $produitconfig->getAppellation()->getKey().";".
        $produitconfig->getMention()->getKey().";".
        $produitconfig->getLieu()->getKey().";".
        $produitconfig->getCouleur()->getKey().";".
        $produitconfig->getCepage()->getKey());
    }
}