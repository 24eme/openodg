<?php

/**
 * Model for Tournee
 *
 */
class Tournee extends BaseTournee {

    protected $degustations_object = array();

    public function constructId() {
        $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->appellation);

        if($this->appellation_complement) {
            $this->identifiant .= $this->appellation_complement;
        }

        if($this->millesime) {
            $this->identifiant .= $this->millesime;
        }

        if ($this->type_tournee == TourneeClient::TYPE_TOURNEE_CONSTAT_VTSGN) {
            $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->agent_unique);
        }

        $this->libelle = null;
        $this->getLibelle();

        $this->set('_id', sprintf("%s-%s", TourneeClient::TYPE_COUCHDB, $this->identifiant));
    }

    public function getProduitHashConfigByAppellation() {
        if(!$this->appellation) {

            return null;
        }
        return $this->getConfiguration()->declaration->certification->genre->get("appellation_".$this->appellation)->getHash();
    }

    public function getProduit() {
        if(!$this->_get('produit')) {

            return $this->getProduitHashConfigByAppellation();
        }

        return $this->_get('produit');
    }

    public function getProduitConfig() {
        if(!$this->_get('produit')) {

            return null;
        }

        return $this->getConfiguration()->get($this->_get('produit'));
    }

    public function getLibelle() {
        if(!$this->_get('libelle')) {
            $this->_set('libelle', $this->constructLibelle());
        }

        return $this->_get('libelle');
    }

    public function getMillesime() {
        if(!$this->_get('millesime') && $this->date) {

            return ConfigurationClient::getInstance()->getCampagneManager()->getCampagneByDate($this->date)."";
        }

        return $this->_get('millesime');
    }

    public function constructLibelle() {
        $libelle = null;
        if($this->getProduitConfig()) {
            $libelle .= $this->getProduitConfig()->getLibelleComplet();
        } elseif($this->appellation) {
            $libelle .= DegustationClient::getInstance()->getAppellationLibelle($this->appellation);
        }

        if($this->millesime) {
            $libelle .= " ".$this->millesime;
        }

        return $libelle;
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->getCampagne());
    }

    public function setDate($date) {
        return $this->_set('date', $date);
    }

    public function getProduits() {
        if ($this->appellation == 'VTSGN') {
            return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
        }

        if($this->appellation == "CREMANT") {
            return $this->getConfiguration()->declaration->certification->genre->appellation_CREMANT->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
        }

        return $this->getConfiguration()->declaration->certification->genre->appellation_ALSACE->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function getOperateursOrderByHour() {
        $operateurs = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            $heure = $degustation->heure;

            if (!$degustation->heure) {
                $heure = TourneeClient::HEURE_NON_REPARTI;
            }
            $operateurs[$heure][sprintf('%05d', $degustation->position) . $degustation->getIdentifiant()] = $degustation;
            ksort($operateurs[$heure]);
        }

        return $operateurs;
    }

    public function getRendezVousOrderByHour() {
        $rdvs = array();
        foreach ($this->rendezvous as $id => $rendezvous) {
            $rdvs[$rendezvous->heure][$id] = $rendezvous;
            ksort($rdvs[$rendezvous->heure]);
        }

        return $rdvs;
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

    public function setMillesime($millesime) {
        if($millesime) {
            $millesime .= "";
        }

        return $this->_set('millesime', $millesime);
    }

    public function getDegustationsObject() {
        if (count($this->degustations_object) == 0) {
            $this->degustations_object = array();

            foreach ($this->degustations as $cvi => $id) {
                $degustation = DegustationClient::getInstance()->find($id);
                if (!$degustation) {
                    continue;
                }
                $this->degustations_object[$cvi] = $degustation;
            }
        }

        return $this->degustations_object;
    }

    public function resetDegustationsObject() {
        $this->degustations_object = array();
    }

    public function getDegustationsObjectByCommission($commission) {
        $degustations = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            if (!$degustation->isInCommission($commission)) {
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
        foreach ($this->getDegustationsObject() as $degustation) {
            $degustation->cleanPrelevements();
        }
    }

    public function generateNotes() {
        foreach ($this->getDegustationsObject() as $degustation) {
            $degustation->generateNotes();
        }
    }

    public function generatePrelevements() {
        foreach ($this->getDegustationsObject() as $degustation) {
            if (count($degustation->prelevements) > 0) {
                return false;
            }
        }
        $j = 100;
        foreach ($this->getDegustationsObject() as $degustation) {
            if (count($degustation->prelevements) > 0) {
                continue;
            }
            $nblots = 0;
            foreach ($degustation->lots as $lot) {
                if (!$lot->prelevement) {
                    $nblots += $lot->nb;
                    continue;
                }
                for ($i = 0; $i < $lot->nb; $i++) {
                    $prelevement = $degustation->addPrelevementFromLot($lot);
                    $prelevement->anonymat_prelevement = $j;
                    $j++;
                }
            }
            for ($i = 1; $i <= $nblots || $i <= 3; $i++) {
                $prelevement = $degustation->prelevements->add();
                $prelevement->anonymat_prelevement = $j;
                $prelevement->preleve = 0;
                $j++;
            }
        }

        return true;
    }

    public function getNbTournees() {
        $nb_tournees = 0; foreach($this->agents as $agent): $nb_tournees += count((array) $agent->dates); endforeach;

        return $nb_tournees;
    }

    public function getNbLots() {
        $nb_lot = 0;
        foreach ($this->getDegustationsObject() as $degustation) {
            $nb_lot += $degustation->getNbLots();
        }

        return $nb_lot;
    }

    public function getOperateursPrelevement() {
        $operateurs = array();

        foreach ($this->operateurs as $operateur) {
            if (!count($operateur->getLotsPrelevement())) {
                continue;
            }

            $operateurs[$operateur->getIdentifiant()] = $operateur;
        }

        return $operateurs;
    }

    public function getOperateursReporte() {
        $degustations = array();

        foreach ($this->getDegustationsObject() as $degustation) {
            if ($degustation->motif_non_prelevement != DegustationClient::MOTIF_NON_PRELEVEMENT_REPORT) {
                continue;
            }

            $degustations[$degustation->getIdentifiant()] = $degustation;
        }

        return $degustations;
    }

    public function getOperateursDegustes() {
        $degustations = array();

        foreach ($this->getDegustationsObject() as $degustation) {
            if (!$degustation->isDeguste()) {
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
        foreach ($this->getDegustationsObject() as $degustation) {
            if (!$degustation->isTourneeTerminee()) {
                return false;
            }
        }

        return true;
    }

    public function isAffectationTerminee() {
        foreach ($this->getDegustationsObject() as $degustation) {
            if (!$degustation->isAffectationTerminee()) {
                return false;
            }
        }

        return true;
    }

    public function isDegustationTerminee() {
        foreach ($this->getDegustationsObject() as $degustation) {
            if (!$degustation->isDegustationTerminee()) {

                return false;
            }
        }

        return true;
    }

    public function updateOperateursFromPrevious() {
        $degustations_json = TourneeClient::getInstance()->getReportes($this->appellation, $this->getCampagne());

        foreach ($degustations_json as $degustation_previous_json) {
            if ($this->degustations->exist($degustation_previous_json->identifiant)) {
                continue;
            }

            $degustation_previous = DegustationClient::getInstance()->find($degustation_previous_json->_id);
            $degustation = $this->addOperateurFromDRev($degustation_previous->drev);

            if (!$degustation) {
                continue;
            }

            $degustation->reporte = $degustation_previous->date_prelevement;

            foreach ($degustation_previous->getLotsPrelevement() as $lot_key => $lot) {
                if (!$degustation->lots->exist($lot_key)) {
                    continue;
                }

                $degustation->lots->get($lot_key)->prelevement = 1;
            }
        }
    }

    public function updateOperateursFromDRev() {
        $prelevements = TourneeClient::getInstance()->getPrelevementsFiltered($this->appellation, $this->date_prelevement_debut, $this->date_prelevement_fin, $this->getCampagne());
        foreach ($prelevements as $prelevement) {
            $degustation = $this->addOperateurFromDRev($prelevement->_id);
        }
    }

    public function updateOperateursFromOthers(){
      $others = TourneeClient::getInstance()->getPrelevementsFiltered($this->appellation, $this->date_prelevement_fin, "2030-12-01", $this->getCampagne());
      foreach ($others as $operateur) {
            $degustation = $this->addOperateurFromDRev($operateur->_id);
      }
    }

    public function getCampagne() {

        return $this->millesime;
    }

    public function cleanOperateurs($save = true) {
        $degustations_to_remove = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            if ($degustation->isAffecteTournee()) {
                continue;
            }

            $degustations_to_remove[] = $degustation->getIdentifiant();
        }

        foreach ($degustations_to_remove as $identifiant) {
            $this->removeDegustation($identifiant, $save);
        }
    }

    public function generateDegustations() {
        foreach ($this->operateurs as $operateur) {
            $degustation = DegustationClient::getInstance()->findOrCreateByTournee($this, $operateur->cvi);
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
        if (!$drev) {

            return null;
        }

        if (!$drev->validation) {

            return null;
        }

        if ($this->degustations->exist($drev->identifiant)) {

            return $this->getDegustationObject($drev->identifiant);
        }

        $degustation = DegustationClient::getInstance()->findOrCreateByTournee($this, $drev->identifiant);

        $degustation->updateFromDRev($drev);
        $degustation->constructId();

        $this->addDegustationObject($degustation);

        return $degustation;
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

        if (!$degustation->isNew() && $save) {
            DegustationClient::getInstance()->delete($degustation);
        }
    }

    public function saveDegustations() {
        foreach ($this->getDegustationsObject() as $degustation) {
            $degustation->save();
        }
    }

    public function updateNombrePrelevements() {
        $nombre_prelevements = 0;
        foreach ($this->getDegustationsObject() as $degustation) {
            $nombre_prelevements += $degustation->getNombrePrelevements();
        }

        $this->nombre_prelevements = $nombre_prelevements;
    }

    public function getNombrePrelevements() {
        if (!$this->_get('nombre_prelevements')) {
            $this->updateNombrePrelevements();
        }

        return $this->_get('nombre_prelevements');
    }

    public function updateNombreCommissionsFromDegustations() {

        $this->nombre_commissions = count($this->getCommissions());
    }

    public function getCommissions() {
        $commissions = array();
        foreach ($this->getDegustationsObject() as $degustation) {
            $commissions = $commissions + $degustation->getCommissions();
        }

        if(count($commissions) < $this->nombre_commissions) {
            for($i = 1; $i <= $this->nombre_commissions; $i++) {
                $commissions[$i+""] = $i+"";
            }
        }

        ksort($commissions);

        return $commissions;
    }

    public function hasSentCourrier() {
        $prelevements = array();
        foreach ($this->operateurs as $cvi => $operateur) {
            foreach ($operateur->prelevements as $prelevement) {
                if (!is_null($prelevement->courrier_envoye)) {
                    return true;
                }
            }
        }

        return false;
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
                if (!is_null($prelevement->courrier_envoye)) {
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

        foreach ($this->getOperateursDegustes() as $operateurDeguste) {

            foreach ($operateurDeguste->prelevements as $prelevement) {
                if ($prelevement->isDeguste()) {
                    $key = sprintf("%03d-%02d-%s-%s", $prelevement->anonymat_degustation, $prelevement->commission, uniqid(), $operateurDeguste->getIdentifiant());
                    $notes[$key] = new stdClass();
                    $notes[$key]->operateur = $operateurDeguste;
                    $notes[$key]->prelevement = $prelevement;
                }
            }
        }

        ksort($notes);

        return $notes;
    }

    public function addDegustateur($type, $compteId) {
        $compte = CompteClient::getInstance()->find($compteId);
        $degustateur = $this->degustateurs->add($type)->add($compteId);
        $degustateur->nom = $compte->nom_a_afficher;
        $degustateur->email = $compte->email;
        $degustateur->adresse = $compte->adresse;
        $degustateur->commune = $compte->commune;
        $degustateur->code_postal = $compte->code_postal;

        return $degustateur;
    }

    /*
     * =========================================
     */

    public function addOrUpdateRendezVous($rendezvous_or_id) {
        $rendezvous = $rendezvous_or_id;
        if (!is_object($rendezvous_or_id)) {
            $rendezvous = RendezvousClient::getInstance()->find($rendezvous_or_id);
        }
        $rendezvousNode = $this->getOrAdd('rendezvous')->getOrAdd($rendezvous->_id);
        $rendezvousNode->compte_identifiant = $rendezvous->identifiant;
        $rendezvousNode->compte_raison_sociale = $rendezvous->raison_sociale;
        $rendezvousNode->compte_adresse = $rendezvous->adresse;
        $rendezvousNode->compte_code_postal = $rendezvous->code_postal;
        $rendezvousNode->compte_commune = $rendezvous->commune;
        $rendezvousNode->compte_cvi = $rendezvous->cvi;
        $rendezvousNode->compte_lon = $rendezvous->lon;
        $rendezvousNode->compte_lat = $rendezvous->lat;
        $rendezvousNode->compte_telephone_mobile = $rendezvous->telephone_mobile;
        $rendezvousNode->compte_telephone_prive = $rendezvous->telephone_prive;
        $rendezvousNode->compte_telephone_bureau = $rendezvous->telephone_bureau;
        $rendezvousNode->compte_email = $rendezvous->email;

        $rendezvousNode->rendezvous_commentaire = $rendezvous->commentaire;
        $rendezvousNode->type_rendezvous = $rendezvous->type_rendezvous;
        $rendezvousNode->heure = $rendezvous->heure;
        $rendezvousNode->nom_agent_origine = $rendezvous->nom_agent_origine;
        if (($rendezvous->statut != RendezvousClient::RENDEZVOUS_STATUT_REALISE) && ($rendezvous->statut != RendezvousClient::RENDEZVOUS_STATUT_ANNULE)) {
            $rendezvous->setStatut(RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE);
        }
        if ($rendezvous->isRendezvousRaisin()) {
            $rendezvous->nom_agent_origine = $this->getFirstAgent()->nom;
        }
        $rendezvous->save();

        return $rendezvousNode;
    }

    public function addRendezVousAndGenerateConstat($rendezvous_or_id) {
        $rendezvous = $rendezvous_or_id;
        if (!is_object($rendezvous_or_id)) {
            $rendezvous = RendezvousClient::getInstance()->find($rendezvous_or_id);
        }
        $rendezvousNode = $this->addOrUpdateRendezVous($rendezvous);
        $constats = ConstatsClient::getInstance()->updateOrCreateConstatFromRendezVous($rendezvous);
        $constats->save();
        $rendezvousNode->set('constat', $constats->_id);
    }

    public function addRendezVousAndReferenceConstatsId($rendezvous_or_id, $constats) {
        $rendezvous = $rendezvous_or_id;
        if (!is_object($rendezvous_or_id)) {
            $rendezvous = RendezvousClient::getInstance()->find($rendezvous_or_id);
        }
        $rendezvousNode = $this->addOrUpdateRendezVous($rendezvous);
        $rendezvousNode->set('constat', $constats->_id);
    }

    public function getFirstAgent() {
        $agents = $this->agents;
        foreach ($agents as $agent) {
            return $agent;
        }
        return null;
    }

    public function getAgentUniqueObj() {
        return CompteClient::getInstance()->find('COMPTE-' . $this->agent_unique);
    }

    public function annuleRendezVous($rendezvous_or_id) {
        $rendezvous = $rendezvous_or_id;
        if (!is_object($rendezvous_or_id)) {
            $rendezvous = RendezvousClient::getInstance()->find($rendezvous_or_id);
        }
        $rendezvoussNode = $this->getOrAdd('rendezvous');
        if ($rendezvoussNode->exist($rendezvous->_id)) {
            $rendezvoussNode->remove($rendezvous->_id);
        }
    }

    public function getOrganisme() {
        if(!$this->exist('organisme') || !$this->_get('organisme')) {

            return DegustationClient::ORGANISME_DEFAUT;
        }

        return $this->_get('organisme');
    }

}
