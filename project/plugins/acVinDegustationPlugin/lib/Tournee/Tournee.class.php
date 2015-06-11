<?php

/**
 * Model for Tournee
 *
 */
class Tournee extends BaseTournee {

    protected $degustations_object = array();

    public function constructId() {
        $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->appellation);
        $this->set('_id', sprintf("%s-%s", TourneeClient::TYPE_COUCHDB, $this->identifiant));
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration("2014");
    }

    public function setDate($date) {
        $dateObject = new DateTime($date);
        $this->date_prelevement_fin = $dateObject->modify("-5 days")->format('Y-m-d');

        return $this->_set('date', $date);
    }

    public function getProduits() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
    }

    public function getOperateursOrderByHour() {
        $operateurs = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            $heure = $degustation->heure;

            if (!$degustation->heure) {
                $heure = TourneeClient::HEURE_NON_REPARTI;
            }
            $operateurs[$heure][sprintf('%05d', $degustation->position).$degustation->getIdentifiant()] = $degustation;
            ksort($operateurs[$heure]);
        }

        return $operateurs;
    }

    public function getTournees() {
        $tournees = array();
        foreach ($this->operateurs as $operateur) {
            if (!$operateur->date_prelevement) {
                continue;
            }

            if (!$operateur->agent) {
                continue;
            }
            if (!array_key_exists($operateur->date_prelevement . $operateur->agent, $tournees)) {
                $tournees[$operateur->date_prelevement . $operateur->agent] = new stdClass();
                $tournees[$operateur->date_prelevement . $operateur->agent]->operateurs = array();
                $agents = $this->agents->toArray();
                $tournees[$operateur->date_prelevement . $operateur->agent]->id_agent = $operateur->agent;
                $tournees[$operateur->date_prelevement . $operateur->agent]->nom_agent = $agents[$operateur->agent]->nom;
                $tournees[$operateur->date_prelevement . $operateur->agent]->date = $operateur->date_prelevement;
            }
            $tournees[$operateur->date_prelevement . $operateur->agent]->operateurs[$operateur->getIdentifiant()] = $operateur;
        }
        ksort($tournees);
        return $tournees;
    }

    public function getOperateurs() {

        return $this->getDegustationsObject();
    }

    public function addDegustationObject($degustation) {
        $this->getDegustationsObject();
        $this->degustations->add($degustation->identifiant, $degustation->_id);
        $this->degustations_object[$degustation->identifiant] = $degustation;
    }

    public function getDegustationObject($cvi) {
        $this->getDegustationsObject();
        
        return $this->degustations_object[$cvi];
    }
 
    public function getDegustationsObject() {
        if(count($this->degustations_object) == 0) {
            $this->degustations_object = array();
            
            foreach($this->degustations as $cvi => $id) {
                $degustation = DegustationClient::getInstance()->find($id);
                if(!$degustation) {
                    continue;
                }
                $this->degustations_object[$cvi] = $degustation;
            } 
        }

        return $this->degustations_object;
    }

    public function getDegustationsObjectByCommission($commission) {
        $degustations = array();
        foreach($this->getDegustationsObject() as $degustation) {
            if(!$degustation->isInCommission($commission)) {
                continue;
            }
            $degustations[$degustation->getIdentifiant()] = $degustation;
        }

        return $degustations;
    }

    public function getTourneeOperateurs($agent, $date) {
        $operateurs = array();
        foreach ($this->operateurs as $operateur) {
            if ($operateur->agent != $agent) {

                continue;
            }

            if ($operateur->date_prelevement != $date) {

                continue;
            }

            $operateurs[$operateur->getIdentifiant()] = $operateur;
        }

        usort($operateurs, 'Tournee::sortDegustationsByPosition');

        return $operateurs;
    }

    public static function sortDegustationsByPosition($degustation_a, $degustation_b) {

        return $degustation_a->position > $degustation_b->position;
    }

    public function cleanPrelevements() {
        foreach($this->getDegustationsObject() as $degustation) {
            $degustation->cleanPrelevements();
        }
    }

    public function generateNotes() {
        foreach($this->getDegustationsObject() as $degustation) {
            $degustation->generateNotes();
        }
    }

    public function generatePrelevements() {
        foreach($this->getDegustationsObject() as $degustation) { if(count($degustation->prelevements) > 0) { return false; } }
        $j = 100;
        foreach ($this->getDegustationsObject() as $degustation) {
            if(count($degustation->prelevements) > 0) {
                continue;
            }
            foreach($degustation->lots as $lot) {
                if(!$lot->prelevement) {
                    continue;
                }
                for($i=0; $i < $lot->nb; $i++) {
                    $prelevement = $degustation->addPrelevementFromLot($lot);
                    $prelevement->anonymat_prelevement = $j;
                    $j++;
                }
            }
            for($i=1; $i <= 2; $i++) {
                $prelevement = $degustation->prelevements->add();
                $prelevement->anonymat_prelevement = $j;
                $prelevement->preleve = 0;
                $j++;
            }
        }

        return true;
    }

    public function getNbLots() {
        $nb_lot = 0;
        foreach($this->getDegustationsObject() as $degustation) {
            $nb_lot += count($degustation->getLotsPrelevement());
        }

        return $nb_lot;
    }

    public function getOperateursPrelevement() {
        $operateurs = array();

        foreach($this->operateurs as $operateur) {
            if(!count($operateur->getLotsPrelevement())) {
                continue;                
            }

            $operateurs[$operateur->getIdentifiant()] = $operateur;
        }

        return $operateurs;
    }

    public function getOperateursReporte() {
        $degustations = array();

        foreach($this->getDegustationsObject() as $degustation) {
            if($degustation->motif_non_prelevement != DegustationClient::MOTIF_NON_PRELEVEMENT_REPORT) {
                continue;
            }

            $degustations[$degustation->getIdentifiant()] = $degustation;
        }

        return $degustations;
    }
    
    public function getOperateursDegustes() {
        $degustations = array();

        foreach($this->getDegustationsObject() as $degustation) {
            if(!$degustation->isDeguste()) {
                continue;
            }

            $degustations[$degustation->getIdentifiant()] = $degustation;
        }

        return $degustations;
    }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

    public function isTourneeTerminee() {
        foreach($this->getDegustationsObject() as $degustation) {
            if(!$degustation->isTourneeTerminee()) {
                return false;
            }
        }

        return true;
    }

    public function isAffectationTerminee() {
        foreach($this->getDegustationsObject() as $degustation) {
            if(!$degustation->isAffectationTerminee()) {
                return false;
            }
        }

        return true;
    }

    public function isDegustationTerminee() {
        foreach($this->getDegustationsObject() as $degustation) {
            if(!$degustation->isDegustationTerminee()) {

                return false;
            }
        }

        return true;
    }

    public function updateOperateursFromPrevious() {
        $degustations_json = TourneeClient::getInstance()->getReportes($this->appellation);

        foreach($degustations_json as $degustation_previous_json) {
            if($this->degustations->exist($degustation_previous_json->identifiant)) {
                continue;
            }

            $degustation_previous = DegustationClient::getInstance()->find($degustation_previous_json->_id);
            $degustation = $this->addOperateurFromDRev($degustation_previous->drev);

            if(!$degustation) {
                continue;
            }

            $degustation->reporte = 1;

            /*foreach($degustation_previous->getLotsPrelevement() as $lot_key => $lot) {
                if(!$degustation->lots->exist($lot_key)) {
                    continue;
                }

                $degustation->lots->get($lot_key)->prelevement = 1;
            }*/
        }
    }

    public function updateOperateursFromDRev() {
        $prelevements = TourneeClient::getInstance()->getPrelevementsFiltered($this->appellation, $this->date_prelevement_debut, $this->date_prelevement_fin);

        foreach($prelevements as $prelevement) {
            $degustation = $this->addOperateurFromDRev($prelevement->_id);
        }
    }

    public function cleanOperateurs($save = true) {
        $degustations_to_remove = array();
        foreach($this->getDegustationsObject() as $degustation) {
            if($degustation->isAffecteTournee()) {
                continue;
            }

            $degustations_to_remove[] = $degustation->getIdentifiant();
        }

        foreach($degustations_to_remove as $identifiant) {
            $this->removeDegustation($identifiant, $save);
        }
    }

    public function generateDegustations() {
        foreach($this->operateurs as $operateur) {
            $degustation = DegustationClient::getInstance()->findOrCreate($operateur->cvi, $this->date, $this->appellation);
            $operateur->updateDegustation($degustation);
            $degustation->save();
            $operateur->degustation = $degustation->_id;
        }
    }

    public function addOperateurFromDRev($drev_id) {

        return $this->addDegustationFromDRev($drev_id);
    }

    public function addDegustationFromDRev($drev_id) {
        $drev = DRevClient::getInstance()->find($drev_id, acCouchdbClient::HYDRATE_DOCUMENT);
        if(!$drev) {

            return null;
        }

        if(!$drev->validation) {
            
            return null;
        }

        if($this->degustations->exist($drev->identifiant)) {

            return $this->getDegustationObject($drev->identifiant);
        }

        $degustation = DegustationClient::getInstance()->findOrCreate($drev->identifiant, $this->date, $this->appellation);

        $degustation->updateFromDRev($drev);
        $degustation->constructId();

        $this->addDegustationObject($degustation);

        return $degustation;
    }

    public function getPrevious() {

        return TourneeClient::getInstance()->getPrevious($this->_id);
    }

    public function validate() {
        $this->validation = date('Y-m-d');
        $this->statut = TourneeClient::STATUT_TOURNEES;
        $this->cleanOperateurs();
        $this->generatePrelevements();
    }

    public function removeDegustation($identifiant, $save = true) {
        $degustation = $this->getDegustationObject($identifiant);
        $this->degustations->remove($identifiant);
        unset($this->degustations_object[$identifiant]);

        if(!$degustation->isNew() && $save) {
            DegustationClient::getInstance()->delete($degustation);
        }
    }

    public function saveDegustations() {
        foreach($this->getDegustationsObject() as $degustation) {
            $degustation->save();
        }
    }

    public function updateNombrePrelevements() {
        $this->nombre_prelevements = 0;
        foreach($this->getDegustationsObject() as $degustation) {
            $this->nombre_prelevements += count($degustation->prelevements);
        }
    }

    public function getPrelevementsReadyForCourrier() {
        $prelevementsByOperateurs = array();

        foreach ($this->operateurs as $cvi => $operateur) {
            foreach ($operateur->prelevements as $prelevement) {
                if ($prelevement->exist('type_courrier') && $prelevement->type_courrier) {
                    if (!array_key_exists($cvi, $prelevementsByOperateurs)) {
                        $prelevementsByOperateurs[$cvi] = new stdClass();
                        $prelevementsByOperateurs[$cvi]->prelevements = array();
                        $prelevementsByOperateurs[$cvi]->operateur = $operateur;
                    }

                    $prelevementsByOperateurs[$cvi]->prelevements[$prelevement->getHash()] = $prelevement;
                }
            }
        }

        return $prelevementsByOperateurs;
    }

    public function getPrelevementsCourrierToSend() {
        $prelevements = array();
        foreach ($this->operateurs as $cvi => $operateur) {
            foreach ($operateur->prelevements as $prelevement) {
                if (!$prelevement->exist('type_courrier') || !$prelevement->type_courrier) {
                    continue;
                }
                if(!is_null($prelevement->courrier_envoye)) {
                    continue;
                }

                if (!$operateur->email) {
                    continue;
                }
                $prelevements[] = $prelevement;
            }
        }

        return $prelevements;
    }

    public function hasAllTypeCourrier() {
        
        
        return $this->countNotTypeCourrier() == 0;
    }

    public function countNotTypeCourrier() {
        $notes = $this->getNotes();
        $i = 0;
        foreach ($notes as $note) {
            if ($note->prelevement->exist('type_courrier') && $note->prelevement->type_courrier) {
                continue;
            }

            $i++;
        }
        
        return $i;
    }

    public function getNotes() {
        
        $notes = array();

        foreach($this->getOperateursDegustes() as $operateurDeguste) {
           
            foreach ($operateurDeguste->prelevements as $prelevement) {
                if($prelevement->anonymat_degustation){
                    $key = sprintf("%03d-%s", $prelevement->anonymat_degustation, $operateurDeguste->getIdentifiant());
                    $notes[$key] = new stdClass();
                    $notes[$key]->operateur = $operateurDeguste;
                    $notes[$key]->prelevement = $prelevement;
                }
            }
        }

        ksort($notes);

        return $notes;
    }

}
