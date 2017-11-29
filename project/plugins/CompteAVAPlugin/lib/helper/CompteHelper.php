<?php

function comptePictoCssClass($compte) {

        if($compte instanceof sfOutputEscaperArrayDecorator) {
            $compte = $compte->getRawValue();
        }

        $compteType = null;
        if(isset($compte['compte_type'])) {
            $compteType = $compte['compte_type'];
        }
        if(isset($compte->compte_type)) {
            $compteType = $compte->compte_type;
        }

        $hasTagEtablissement = false;

        if($compteType && isset($compte['tags']['automatique']) && in_array('etablissement', $compte['tags']['automatique'])) {
            $hasTagEtablissement = true;
        }

        if($compteType == CompteClient::TYPE_COMPTE_ETABLISSEMENT || $compte instanceof Etablissement || $hasTagEtablissement){

            return "glyphicon glyphicon-home";
        }

        if($compteType == CompteClient::TYPE_COMPTE_SOCIETE || $compte instanceof Societe){

            return "glyphicon glyphicon-calendar";
        }

    return "glyphicon glyphicon-user";
}


function formatSIRET($siret) {
  //return $siret;
  return preg_replace('/^(\d\d\d)(\d\d\d)(\d\d\d)/', '\1 \2 \3 ', $siret);
}
