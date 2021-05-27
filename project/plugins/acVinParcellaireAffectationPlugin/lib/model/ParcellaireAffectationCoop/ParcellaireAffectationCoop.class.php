<?php
/**
 * Model for ParcellaireAffectationCoop
 *
 */

class ParcellaireAffectationCoop extends BaseParcellaireAffectationCoop {


    public function constructId() {
        $this->set('_id', ParcellaireAffectationCoopClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$this->periode);
    }

    public function initDoc($identifiant, $periode, $date) {
        $this->identifiant = $identifiant;
        $this->campagne = $periode.'-'.($periode + 1);
        $this->constructId();
    }

    public function getPeriode() {
        return preg_replace('/-.*/', '', $this->campagne);
    }

    public function getEtablissementObject() {

          return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function buildApporteurs($sv11){
        $apporteurs = $this->apporteurs;

        // Depuis les liaisons
        foreach($this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR) as $liaison) {
            $apporteur = $apporteurs->getOrAdd($liaison->id_etablissement);
            $apporteur->nom = $liaison->libelle_etablissement;
            $apporteur->cvi = $liaison->cvi;
            $apporteur->provenance = "liaison";
        }

        // Depuis la SV11
        foreach($sv11->getApporteurs() as $idApporteur => $nom) {
            $etb = EtablissementClient::getInstance()->find($idApporteur);
            $apporteur = $apporteurs->getOrAdd($idApporteur);
            $apporteur->nom = $nom;
            $apporteur->cvi = $etb->cvi;
            $apporteur->provenance = "sv11";
        }

    }

}
