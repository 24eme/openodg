<?php

/**
 * Model for Constat
 *
 */
class Constat extends BaseConstat {

    public function createOrUpdateFromRendezVous(Rendezvous $rdv) {
        if ($rdv->isRendezvousRaisin()) {
            $this->date_raisin = $rdv->getDateHeure();
            $this->statut_raisin = ConstatsClient::STATUT_NONCONSTATE;
            $this->statut_volume = ConstatsClient::STATUT_NONCONSTATE;
            $this->rendezvous_raisin = $rdv->_id;
        } elseif ($rdv->isRendezvousVolume()) {
            $this->rendezvous_volume = $rdv->_id;
            $this->date_volume = $rdv->getDateHeure();
        }
    }

    public function updateConstat($jsonContent) {
        
        $this->produit = $jsonContent->produit;
        $this->produit_libelle = $jsonContent->produit_libelle;
        $this->nb_botiche = $jsonContent->nb_botiche;
        $this->contenant = $jsonContent->contenant;
        $this->contenant_libelle = $jsonContent->contenant_libelle;
        $this->degre_potentiel_raisin = $jsonContent->degre_potentiel_raisin;
        $this->degre_potentiel_volume = (isset($jsonContent->degre_potentiel_volume)) ? $jsonContent->degre_potentiel_volume : null;
        $this->volume_obtenu = (isset($jsonContent->volume_obtenu)) ? $jsonContent->volume_obtenu : null;
        $this->type_vtsgn = (isset($jsonContent->type_vtsgn)) ? $jsonContent->type_vtsgn : null;
        $this->rendezvous_raisin = $jsonContent->rendezvous_raisin;
        
        $this->raison_refus = (isset($jsonContent->raison_refus)) ? $jsonContent->raison_refus : null;
        $this->raison_refus_libelle = (isset($jsonContent->raison_refus_libelle)) ? $jsonContent->raison_refus_libelle : null;
        
        if ($jsonContent->type_constat == 'raisin') {
            $this->setStatutRaisinAndCreateVolumeRendezvous($jsonContent);
        }
        if ($jsonContent->type_constat == 'volume') {
            $this->setStatutVolumeAndRendezvous($jsonContent);
        }
        
        $this->statut_volume = $jsonContent->statut_volume;
        $this->statut_raisin = $jsonContent->statut_raisin;
    }

    public function setStatutRaisinAndCreateVolumeRendezvous($jsonContent) {
       
        if ($jsonContent->statut_raisin == ConstatsClient::STATUT_APPROUVE) {
            $newRdvVolume = RendezvousClient::getInstance()->findOrCreateRendezvousVolumeFromIdRendezvous($jsonContent->rendezvous_raisin,$jsonContent->nom_agent_origine);
            $newRdvVolume->save();
            $rendezvousRaisin = RendezvousClient::getInstance()->find($jsonContent->rendezvous_raisin);
            $rendezvousRaisin->set('statut', RendezvousClient::RENDEZVOUS_STATUT_REALISE);
            $rendezvousRaisin->save();

            $tourneeOrigine = TourneeClient::getInstance()->findTourneeByIdRendezvous($jsonContent->rendezvous_raisin);
            $newTournee = TourneeClient::getInstance()->findOrAddByDateAndAgent($newRdvVolume->getDate(), $tourneeOrigine->getAgentUniqueObj());
            $newTournee->addRendezVousAndReferenceConstatsId($newRdvVolume->_id, $this->getDocument());
            $newTournee->save();
            $this->date_volume = str_replace('-', '', $newTournee->getDate()) . substr($this->date_raisin, 8, 4);
            $this->rendezvous_volume = $newRdvVolume->_id;
        }
    }

    public function setStatutVolumeAndRendezvous($jsonContent) {  
        if (($this->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($this->statut_volume == ConstatsClient::STATUT_NONCONSTATE)) {
            $rendezvousVolume = RendezvousClient::getInstance()->find($jsonContent->rendezvous_volume);
            $rendezvousVolume->set('statut', RendezvousClient::RENDEZVOUS_STATUT_REALISE);
            $rendezvousVolume->save();
        }
    }

    public function determineTypeConstat() {
        if ($this->statut_raisin == ConstatsClient::STATUT_NONCONSTATE) {
            return ConstatsClient::CONSTAT_TYPE_RAISIN;
        }
        return ConstatsClient::CONSTAT_TYPE_VOLUME;
    }

    public function isConstatVolume() {
        return ($this->determineTypeConstat() == ConstatsClient::CONSTAT_TYPE_RAISIN) && $this->date_volume;
    }

}
