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

    public function buildApporteurs($sv11 = null){

        $apporteurs = $this->apporteurs;
        $sv11Apporteurs = $sv11->getApporteurs();
        $apporteursArray = array();

        // Depuis les liaisons
        foreach($this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR) as $liaison) {
            $apporteursArray[$liaison->id_etablissement] = $liaison->libelle_etablissement;
        }

        // Depuis la SV11
        foreach($sv11Apporteurs as $idApporteur => $nom) {
            $apporteursArray[$idApporteur] = $nom;
        }

        asort($apporteursArray);

        foreach ($apporteursArray as $id => $nom ) {
            $etb = EtablissementClient::getInstance()->find($id);
            if(!$etb->cvi){
                continue;
            }
            $apporteur = $apporteurs->getOrAdd($id);
            $apporteur->nom = $etb->nom;
            $apporteur->cvi = $etb->cvi;
            $apporteur->provenance = (array_key_exists($id, $sv11Apporteurs))? SV11Client::TYPE_MODEL : "";
        }
    }

}
