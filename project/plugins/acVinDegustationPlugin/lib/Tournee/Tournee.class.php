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

        return $this->getConfiguration()->declaration->certification->genre->appellation_ALSACE->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function getOperateursOrderByHour() {
        $operateurs = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            $heure = $degustation->heure;

            if (!$degustation->heure) {
                $heure = "24:00";
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

    public function getDegustationObject($cvi) {

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

    public function getPrelevementsByNumeroDegustation($commission) {
        $prelevements = array();

        foreach($this->operateurs as $operateur) {
            foreach($operateur->prelevements as $prelevement) {
                if($prelevement->commission != $commission) {
                    continue;
                }
                $cepage_key = substr($prelevement->hash_produit, -2);
                $prelevements["P".TourneeClient::$ordre_cepages[$cepage_key].sprintf("%03d", $prelevement->anonymat_degustation)] = $prelevement;
            }
        }

        ksort($prelevements);

        $prelevements_return = array();

        foreach($prelevements as $prelevement) {
            $prelevements_return[$prelevement->anonymat_degustation] = $prelevement;
        }

        return $prelevements_return;
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
                    $prelevement = $degustation->prelevements->add();
                    $prelevement->hash_produit = $lot->hash_produit;
                    $prelevement->libelle = $lot->libelle;
                    $prelevement->anonymat_prelevement = $j;
                    $prelevement->preleve = 1;
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

    public function getOperateursPrelevement() {
        $operateurs = array();

        foreach($this->operateur as $operateur) {
            if(!count($operateur->getLotsPrelevement())) {
                continue;                
            }

            $operateurs[$operateur->getKey()] = $operateur;
        }

        return $operateurs;
    }

    public function getOperateursReporte() {
        $operateurs = array();

        foreach($this->operateurs as $operateur) {
            if($operateur->isPrelever()) {
                continue;
            }

            $operateurs[$operateur->getKey()] = $operateur;
        }

        return $operateurs;
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
    
    public function getNotes() {
        
        $notes = array();

        foreach($this->getOperateursDegustes() as $operateurDeguste) {
           
            foreach ($operateurDeguste->prelevements as $prelevement) {
                if($prelevement->anonymat_degustation){
                    $notes[$operateurDeguste->getIdentifiant().'-'.$prelevement->anonymat_degustation] = new stdClass();
                    $notes[$operateurDeguste->getIdentifiant().'-'.$prelevement->anonymat_degustation]->operateur = $operateurDeguste;
                    $notes[$operateurDeguste->getIdentifiant().'-'.$prelevement->anonymat_degustation]->prelevement = $prelevement;
                }
            }
        }
        return $notes;
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

    /*public function updateOperateursFromPrevious() {
        $previous = $this->getPrevious();

        if(!$previous) {

            return null;
        }

        foreach($previous->getOperateursReporte() as $o) {
            $operateur = $this->addOperateurFromDRev("DREV-".$o->getKey()."-".ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());

            if(!$operateur) {
                continue;
            }

            $operateur->reporte = 1;

            if(count($operateur->getLotsPrelevement()) > 0) {

                continue;
            }
            
            foreach($o->lots as $lot) {
                $lot_key = str_replace("cepage-", "cepage_", str_replace("appellation-ALSACE", "appellation_ALSACE", str_replace("_", "-", $lot->getKey())));
                if(!$operateur->lots->exist($lot_key)) {
                    continue;
                }
                $operateur->lots->get($lot_key)->prelevement = 1;
            }
        }
    }*/

    public function updateOperateursFromDRev() {
        $prelevements = TourneeClient::getInstance()->getPrelevements($this->date_prelevement_debut, $this->date_prelevement_fin);

        $previous = $this->getPrevious();

        foreach($prelevements as $prelevement) {
            $operateur = $this->addOperateurFromDRev($prelevement->_id);

            /*if($previous && $previous->operateurs->exist($operateur->cvi) && $previous->operateurs->get($operateur->cvi)->isPrelever()) {
                $this->operateurs->remove($operateur->cvi);
            }*/
        }
    }

    public function cleanOperateurs($save = true) {
        $degustations_to_remove = array();
        foreach($this->getDegustationsObject() as $degustation) {
            if($degustation->date_prelevement && $degustation->agent) {
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
        $drev = DRevClient::getInstance()->find($drev_id, acCouchdbClient::HYDRATE_JSON);
        
        if(!$drev) {

            return null;
        }

        if(!$drev->validation) {
            
            return null;
        }

        $degustation = DegustationClient::getInstance()->findOrCreate($drev->identifiant, $this->appellation, $this->date);
        $degustation->drev = $drev->_id;

        $degustation->updateFromDRev($drev);
        $degustation->constructId();

        $this->degustations->add($degustation->identifiant, $degustation->_id);
        $this->degustations_object[$degustation->identifiant] = $degustation;

        return $degustation;
    }

    public function getPrevious() {

        return TourneeClient::getInstance()->getPrevious($this->_id);
    }

    public function validate() {
        $this->validation = date('Y-m-d');
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

}
