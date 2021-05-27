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

    public function getApporteursChoisis() {
        $apporteurs = array();
        foreach($this->apporteurs as $apporteur) {
            if(!$apporteur->apporteur) {
                continue;
            }
            $apporteurs[$apporteur->getKey()] = $apporteur;
        }

        return $apporteurs;
    }

    protected function addApporteur($id_etablissement) {
        if($this->apporteurs->exist($id_etablissement)) {

            return $this->apporteurs->get($id_etablissement);
        }
        $etablissement = EtablissementClient::getInstance()->find($id_etablissement, acCouchdbClient::HYDRATE_JSON);
        if(!$etablissement) {
            return;
        }
        $apporteur = $this->apporteurs->add($etablissement->_id);
        $apporteur->nom = $etablissement->nom;
        $apporteur->cvi = $etablissement->cvi;
        $apporteur->intention = true;
        $apporteur->apporteur = true;

        return $apporteur;
    }

    public function buildApporteurs($sv11){
        $apporteurs = $this->apporteurs;

        // Depuis les liaisons
        foreach($this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR) as $liaison) {
            $apporteur = $this->addApporteur($liaison->id_etablissement);
        }

        // Depuis la SV11
        foreach($sv11->getApporteurs() as $idApporteur => $nom) {
            $apporteur = $this->addApporteur($idApporteur);
            $apporteur->provenance = SV11Client::TYPE_MODEL ;
        }
    }

    public function updateApporteurs() {
        foreach($this->getApporteursChoisis() as $apporteur) {
            if($apporteur->getAffectationParcellaire()) {
                continue;
            }

            $apporteur->intention = true;
            $affectation = $apporteur->createAffectationParcellaire();

            if(count($affectation->getParcelles())) {
                continue;
            }

            $apporteur->intention = false;
        }
    }

}
