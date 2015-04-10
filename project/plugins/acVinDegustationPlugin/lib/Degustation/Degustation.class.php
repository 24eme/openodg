<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation {
    public function constructId() {
        $this->set('_id', sprintf("%s-%s-%s-%s", DegustationClient::TYPE_COUCHDB, $this->cvi, str_replace("-", "", $this->date_degustation), $this->appellation));
    }

    public function updateFromDRev($drev = null) {
        if(!$drev) {
            $drev = DRevClient::getInstance()->find($this->drev, acCouchdbClient::HYDRATE_JSON);
        }

        $this->cvi = $drev->declarant->cvi;
        $this->raison_sociale = $drev->declarant->raison_sociale;
        $this->adresse = $drev->chais->cuve_->adresse;
        $this->code_postal = $drev->chais->cuve_->code_postal;
        $this->commune = $drev->chais->cuve_->commune;
        

        $prelevement = $drev->prelevements->cuve_ALSACE;
        $this->date_demande = $prelevement->date;

        foreach($prelevement->lots as $l_key => $l) {
            if(!$l->nb_hors_vtsgn) {
                continue;
            }
            $lot = $this->lots->add(str_replace("/", "-", $l->hash_produit));
            $lot->hash_produit = $l->hash_produit;
            $lot->libelle = $l->libelle;
            $lot->nb = $l->nb_hors_vtsgn;
        }
    }

    public function getAppellationLibelle() {
        if(!$this->appellation) {
            return null;
        }

        if(!$this->_get('appellation_libelle')) {
            $appellationsWithLibelle = TourneeCreationForm::getAppellationChoices();
            $this->_set("appellation_libelle", $appellationsWithLibelle[$this->appellation]);
        }

        return $this->_get('appellation_libelle');
    }

    public function isDeguste() {
          foreach($this->prelevements as $prelevement) {
            if(!is_null($prelevement->anonymat_degustation)) {
                return true;
            }
        }

        return false;
    }

    public function getLotsPrelevement() {
        $lots = array();

        foreach($this->lots as $lot) {
            if(!$lot->prelevement) {
                continue;
            }

            $lots[$lot->getKey()] = $lot;
        }

        return $lots;
    }

    public function resetLotsPrelevement() {
        foreach($this->lots as $lot) {
            $lot->prelevement = 0;
        }
    }

    public function getPrelevementsByAnonymatPrelevement($anonymat_prelevement) {
        foreach($this->prelevements as $prelevement) {
            if($prelevement->anonymat_prelevement == $anonymat_prelevement) {

                return $prelevement;
            }
        }

        return null;
    }

    public function getPrelevementsByAnonymatDegustation($anonymat_degustation) {
        foreach($this->prelevements as $prelevement) {
            if($prelevement->anonymat_degustation == $anonymat_degustation) {

                return $prelevement;
            }
        }

        return null;
    }

    public function cleanPrelevements() {
        $hash_to_delete = array();

        foreach($this->prelevements as $prelevement) {
            if($prelevement->isPreleve()) {
                continue;
            }
            $hash_to_delete[$prelevement->getHash()] = $prelevement->getHash();
        }

        krsort($hash_to_delete);

        foreach($hash_to_delete as $hash) {
            $this->remove($hash);
        }
    }

    public function generateNotes() {
         foreach($this->prelevements as $prelevement) {
            foreach(DegustationClient::$note_type_libelles as $key_type_note => $libelle_type_note) {
                    $prelevement->notes->add($key_type_note);
            }
        }
    }

    public function isTourneeTerminee() {
        if($this->motif_non_prelevement)  {

            return true;
        }

        foreach($this->prelevements as $prelevement) {

            if($prelevement->isPreleve()) {

                return true;
            }
        }

        return false;
    }

    public function isAffectationTerminee() {
        foreach($this->prelevements as $prelevement) {
            if(!$prelevement->isAffectationTerminee()) {

                return false;
            }
        }

        return true;
    }

    public function isDegustationTerminee() {
        foreach($this->prelevements as $prelevement) {
            if(!$prelevement->isDegustationTerminee()) {

                return false;
            }
        }

        return true;
    }

    public function isInCommission($commission) {
        foreach($this->prelevements as $prelevement) {
            if($prelevement->commission == $commission) {

                return true;
            }
        }

        return false;
    }

    public function generateCourrier() {
        
    }

    public function getCompte() {

        return CompteClient::getInstance()->findByIdentifiant("E" . $this->getIdentifiant());
    }

    public function updateFromCompte() {
        $compte = $this->getCompte();
        
        $this->email = $compte->email;
        $this->telephone_bureau = $compte->telephone_bureau;
        $this->telephone_prive = $compte->telephone_prive;
        $this->telephone_mobile = $compte->telephone_mobile;
        $chai = $compte->findChai($this->adresse, $this->commune, $this->code_postal);

        if($chai) {
            $this->lat = $chai->lat;
            $this->lon = $chai->lon;
        }

        if(!$this->lat || !$this->lon) {
            $coordonnees = $compte->calculCoordonnees($this->adresse, $this->commune, $this->code_postal);
            if($coordonnees) {
                $this->lat = $coordonnees["lat"];
                $this->lon = $coordonnees["lon"];
            }
        }
    }
}