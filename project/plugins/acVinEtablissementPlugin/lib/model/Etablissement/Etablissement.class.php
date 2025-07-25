<?php

class Etablissement extends BaseEtablissement implements InterfaceCompteGenerique {

    protected $droit = null;
    protected $societe = null;

    public function constructId() {
        $this->set('_id', 'ETABLISSEMENT-' . $this->identifiant);

        $this->statut = is_null($this->statut) ? EtablissementClient::STATUT_ACTIF : $this->statut;
    }

    public function setCompte($c) {
      return $this->_set('compte', $c);
    }

    public function getMasterCompte() {
        if ($this->compte) {
            return CompteClient::getInstance()->find($this->compte) ?: $this->getSociete()->getCompte($this->compte);
        } else {
            return $this->getSociete()->getCompte($this->getSociete()->compte_societe);
        }
    }

    public function getContact() {

        return $this->getMasterCompte();
    }

    public function getSociete() {
      if (!$this->societe) {
          $this->societe = SocieteClient::getInstance()->findSingleton($this->id_societe);
      }
      return $this->societe;
    }

    public function setSociete($s) {
      $this->societe = $s;
    }

    public function isSameAdresseThanSociete() {
        return $this->isSameAdresseThan($this->getSociete()->getMasterCompte());
    }

    public function isSameContactThanSociete() {
        return $this->isSameContactThan($this->getSociete()->getMasterCompte());
    }


    public function getNoTvaIntraCommunautaire() {
        $societe = $this->getSociete();

        if (!$societe) {

            return null;
        }

        return $societe->no_tva_intracommunautaire;
    }

    public function getDenomination() {

        return ($this->nom) ? $this->nom : $this->raison_sociale;
    }

    public function existLiaison($type, $etablissementId) {

        return $this->liaisons_operateurs->exist($type . '_' . $etablissementId);
    }

    public function getLiaisonByTypeAndEtablissementId($type, $etablissementId) {
        return $this->liaisons_operateurs->get($type . '_' . $etablissementId);
    }

    public function addLiaison($type, $etablissement,$saveOther = true, $chai = null, $attributsChai = array()) {

        if(!$etablissement instanceof Etablissement) {
            $etablissement = EtablissementClient::getInstance()->find($etablissement);
        }

        if (!in_array($type, array_keys(EtablissementClient::getTypesLiaisons()))) {
            throw new sfException("liaison type \"$type\" unknown");
        }

        $liaison = $this->liaisons_operateurs->add($type . '_' . $etablissement->_id);

        $liaison->type_liaison = $type;
        $liaison->id_etablissement = $etablissement->_id;
        $liaison->libelle_etablissement = $etablissement->nom;

        $libellesTypeRelation = EtablissementClient::getTypesLiaisons();

        if($etablissement->exist('ppm') && $etablissement->ppm){
          $liaison->ppm = $etablissement->ppm;
        }
        if($etablissement->exist('cvi') && $etablissement->cvi){
          $liaison->cvi = $etablissement->cvi;
        }

        if(EtablissementClient::isTypeLiaisonCanHaveChai($liaison->type_liaison) && $chai) {
            $liaison->hash_chai = $chai->getHash();
            $liaison->add("attributs_chai", $attributsChai);
        }

        if ($saveOther) {
            $this->updateLiaisonOpposee($liaison);
            $this->save();
        }

        return $liaison;
    }

    protected function updateLiaisonOpposee($liaison) {
        $etablissement = $liaison->getEtablissement();
        $typeLiaisonOpposee = EtablissementClient::getTypeLiaisonOpposee($liaison->type_liaison);

        if ($this->isSuspendu()) {
            if ($etablissement->existLiaison($typeLiaisonOpposee, $this->_id)) {
                $etablissement->removeLiaison($etablissement->getLiaisonByTypeAndEtablissementId($typeLiaisonOpposee, $this->_id)->getkey(), false);
                $etablissement->save();
            }
            return;
        }
        if ($etablissement->existLiaison($typeLiaisonOpposee, $this->_id)) {
            return ;
        }

        $chaiOppose = null;
        $attributsChaiOpposes = array();

        if(EtablissementClient::isTypeLiaisonCanHaveChai($typeLiaisonOpposee) && $liaison->getChai()) {
            $chaiOppose = $liaison->getChai();
            $attributsChaiOpposes = $attributsChai;
        }

        if($typeLiaisonOpposee) {
            $etablissement->addLiaison($typeLiaisonOpposee, $this, false, $chaiOppose, $attributsChaiOpposes);
            $etablissement->save();
        }
    }

    public function updateLiaisonsOpposees() {
        foreach($this->liaisons_operateurs as $k => $l) {
            $this->updateLiaisonOpposee($l);
        }
    }

    public function removeLiaison($key, $removeOther = true) {
        if(!$this->liaisons_operateurs->exist($key)) {

            return;
        }

        $liaison = $this->liaisons_operateurs->get($key);

        $typeLiaisonOpposee = EtablissementClient::getTypeLiaisonOpposee($liaison->type_liaison);

        if($removeOther && $typeLiaisonOpposee) {
            $etablissement = $liaison->getEtablissement();
            $etablissement->removeLiaison($typeLiaisonOpposee."_".$this->_id, false);
            $etablissement->save();
        }

        $this->liaisons_operateurs->remove($key);

    }

    public function hasLiaisonsChai(){
        foreach ($this->liaisons_operateurs as $liaison) {
            if($liaison->getChai()){
                return true;
            }
        }
        return false;
    }

    public function isNegociant() {
        return ($this->famille == EtablissementFamilles::FAMILLE_NEGOCIANT);
    }
    public function isCooperative() {
        return ($this->famille == EtablissementFamilles::FAMILLE_COOPERATIVE);
    }

    public function isViticulteur() {
        return ($this->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR);
    }

    public function isNegociantVinificateur() {
        return ($this->famille == EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR);
    }

    public function isCourtier() {
        return ($this->famille == EtablissementFamilles::FAMILLE_COURTIER);
    }
    public function isRepresentant() {
        return ($this->famille == EtablissementFamilles::FAMILLE_REPRESENTANT);
    }



    public function getFamilleType() {
        $familleType = array(EtablissementFamilles::FAMILLE_PRODUCTEUR => 'vendeur',
            EtablissementFamilles::FAMILLE_NEGOCIANT => 'acheteur',
            EtablissementFamilles::FAMILLE_COURTIER => 'mandataire');
        return $familleType[$this->famille];
    }

    public function getDepartement() {
        if ($this->siege->code_postal) {
            return substr($this->siege->code_postal, 0, 2);
        }
        return null;
    }

    public function getDroit() {
        if (is_null($this->droit)) {

            $this->droit = new EtablissementDroit($this);
        }

        return $this->droit;
    }

    public function hasDroit($droit) {

        return $this->getDroit()->has($droit);
    }

    public function isInterpro() {
        return ($this->region != EtablissementClient::REGION_HORS_REGION);
    }

    protected function initFamille() {
        if (!$this->famille) {
            $this->famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        }
    }

    public function save() {
        if(SocieteConfiguration::getInstance()->isDisableSave()) {

            throw new Exception("L'enregistrement des sociétés, des établissements et des comptes sont désactivés");
        }

        $societe = $this->getSociete();

        $compte = $societe->findOrCreateCompteFromEtablissement($this);

        $compte->addOrigine($this->_id);

        $this->pushContactAndAdresseTo($compte);

        $compte->id_societe = $this->getSociete()->_id;
        $compte->nom = $this->nom;
        $compte->statut = $this->statut;
        $compte->commentaire = $this->commentaire;

        $this->compte = $compte->_id;

        $this->updateSecteurs();

        if($this->isSameAdresseThanSociete()) {
            $this->pullAdresseFrom($this->getSociete()->getMasterCompte());
        }
        if($this->isSameContactThanSociete()) {
            $this->pullContactFrom($this->getSociete()->getMasterCompte());
        }
        $this->initFamille();
        $this->raison_sociale = $societe->raison_sociale;
        $this->siret = $societe->siret;
        $this->interpro = "INTERPRO-declaration";
        if(ConfigurationClient::getInstance()->isGiilda() && VracConfiguration::getInstance()->getRegionDepartement() !== false) {
            $this->region = EtablissementClient::getInstance()->calculRegion($this);
        }

        $needSocieteSave = false;
        if($this->isNew()) {
          $needSocieteSave = true;
          $societe->addEtablissement($this);
        }

        if($compte->region != $this->region) {
            $needSocieteSave = true;
        }

        $compte->tags->remove('secteur');
        $compte->tags->add('secteur');

        if (count(EtablissementClient::getSecteurs()) && $this->getSecteurs()) {
            foreach ($this->getSecteurs() as $secteur) {
                $compte->addTag('secteur', $secteur);
            }
        }
        parent::save();

        $this->getMasterCompte()->setStatut($this->getStatut());

        if($needSocieteSave) {
            $societe->save();
        }
        $compte->save();
    }

    public function getSecteurs()
    {
        $secteurs = [];
        if ($this->secteur) {
            $secteurs[] = $this->secteur;
        }
        if ($this->exist('chais')) {
            foreach ($this->chais as $chais => $infos) {
                if ($infos->secteur) {
                    $secteurs[] = $infos->secteur;
                }
            }
        }
        return array_unique($secteurs);
    }

    public function updateSecteurs() {
        if (!CommunesConfiguration::getInstance()->hasSecteurs()) {
            return;
        }
        $secteurs = [];
        if ($this->exist('chais')) foreach($this->chais as $c) {
            if (!$c->secteur) {
                $insee = CommunesConfiguration::getInstance()->findCodeCommune($c->commune);
                if ($insee) {
                    $c->secteur = CommunesConfiguration::getInstance()->getSecteurFromInsee($insee);
                    $secteurs[] = $c->secteur;
                }
            }
        }
        if(!$this->secteur || CommunesConfiguration::getInstance()->hasSecteurAuto()) {
            if (!$this->insee) {
                $this->insee = CommunesConfiguration::getInstance()->findCodeCommune($this->commune);
            }
            if (!$this->insee && $this->cvi) {
                $this->insee = substr($this->cvi, 0, 5);
            }
            if ($this->insee) {
                $this->secteur = CommunesConfiguration::getInstance()->getSecteurFromInsee($this->insee, $this->code_postal);
            } else {
                $secteurs = array_unique($secteurs);
                if (count($secteurs) == 1) {
                    $this->secteur = $secteurs[0];
                }
            }
        }
    }

    public function delete() {
      $this->getSociete()->removeEtablissement($this);
      parent::delete();
    }

    public function isActif() {
        return $this->statut && ($this->statut == EtablissementClient::STATUT_ACTIF);
    }

     public function isSuspendu() {
        return $this->statut && ($this->statut == SocieteClient::STATUT_SUSPENDU);
    }


    public function setIdSociete($id) {
        if (!isset($_ENV['DRY_RUN'])) {
            $soc = SocieteClient::getInstance()->findSingleton($id);
            if (!$soc) {
                throw new sfException("$id n'est pas une société connue");
            }
        }
        $this->_set("id_societe", $id);
    }

    public function __toString() {

        return sprintf('%s (%s)', $this->nom, $this->identifiant);
    }

    public function getBailleurs() {
        $bailleurs = array();
        if (!(count($this->liaisons_operateurs)))
            return $bailleurs;
        $liaisons = $this->liaisons_operateurs;
        foreach ($liaisons as $key => $liaison) {
            if ($liaison->type_liaison == EtablissementClient::TYPE_LIAISON_BAILLEUR)
                $bailleurs[$key] = $liaison;
        }
        return $bailleurs;
    }

    public function findBailleurByNom($nom) {
        $bailleurs = $this->getBailleurs();
        foreach ($bailleurs as $key => $liaison) {
            if ($liaison->libelle_etablissement == str_replace("&", "", $nom))
                return EtablissementClient::getInstance()->find($liaison->id_etablissement);
            if ($liaison->exist('aliases'))
                foreach ($liaison->aliases as $alias) {
                    if (strtoupper($alias) == strtoupper(str_replace("&", "", $nom)))
                        return EtablissementClient::getInstance()->find($liaison->id_etablissement);
                }
        }
        return null;
    }

    public function addAliasForBailleur($identifiant_bailleur, $alias) {
        $bailleurNameNode = EtablissementClient::TYPE_LIAISON_BAILLEUR . '_' . $identifiant_bailleur;
        if (!$this->liaisons_operateurs->exist($bailleurNameNode))
            throw new sfException("La liaison avec le bailleur $identifiant_bailleur n'existe pas");
        if (!$this->liaisons_operateurs->$bailleurNameNode->exist('aliases'))
            $this->liaisons_operateurs->$bailleurNameNode->add('aliases');
        $this->liaisons_operateurs->$bailleurNameNode->aliases->add(str_replace("&amp;", "", $alias), str_replace("&amp;", "", $alias));
    }

    public function getSiegeAdresses() {
        $a = $this->siege->adresse;
        if ($this->siege->exist("adresse_complementaire")) {
            $a .= ' ; ' . $this->siege->adresse_complementaire;
        }
        return $a;
    }
    public function getEmails(){
        if (!$this->email) {
            return array();
        }
        return explode(';',$this->email);
    }

    public function getUniqueEmail() {
    	$emails = $this->getEmails();
    	return (isset($emails[0]))? $emails[0] : null;
    }

    public function findEmail() {
        $etablissementPrincipal = $this->getSociete()->getEtablissementPrincipal();
        if ($this->_get('email')) {
            return $this->get('email');
        }
        if (($etablissementPrincipal->identifiant == $this->identifiant) || !$etablissementPrincipal->exist('email') || !$etablissementPrincipal->email) {
            return false;
        }
        return $etablissementPrincipal->get('email');
    }

    public function getEtablissementPrincipal() {
        return SocieteClient::getInstance()->findSingleton($this->id_societe)->getEtablissementPrincipal();
    }

    public function hasCompteTeledeclarationActivate() {
        return $this->getSociete()->getMasterCompte()->isTeledeclarationActive();
    }

    public function getTeledeclarationEmail() {
        if ($this->exist('teledeclaration_email') && $this->_get('teledeclaration_email')) {
            return $this->_get('teledeclaration_email');
        }
    	if ($compteSociete = $this->getMasterCompte()) {
	        if ($compteSociete->exist('societe_information') && $compteSociete->societe_information->exist('email') && $compteSociete->societe_information->email) {
	            return $compteSociete->societe_information->email;
	        }
	        return $compteSociete->email;
        }
        if ($this->exist('email') && $this->email) {
            return $this->email;
        }
        return null;
    }

    public function setEmailTeledeclaration($email) {
        $this->add('teledeclaration_email', $email);
    }

    public function hasRegimeCrd() {
        return $this->exist('crd_regime') && $this->crd_regime;
    }


    public function getCommentaires() {
        $lines = explode("\n", str_replace(' - ', "\n", $this->getCommentaire()));
        return array_filter($lines, fn($value) => (rtrim($value)));
    }

    public function addCommentaire($s) {
        $c = $this->get('commentaire');
        if ($c) {
            return $this->_set('commentaire', $c . "\n" . $s);
        }
        return $this->_set('commentaire', $s);
    }

    public function getNatureLibelle() {
        if(!$this->exist('nature_inao') || !$this->nature_inao){
            return null;
        }
        return EtablissementClient::getInstance()->getNatureInaoLibelle($this->nature_inao);
    }

    public function hasLegalSignature() {
      return $this->getSociete()->hasLegalSignature();
    }

    public function hasFamille($famille) {

        return $this->famille == $famille;
    }

    public function getSiret() {
    	if (!$this->_get('siret')) {
    		$this->siret = $this->getSociete()->getSiret();
    	}
    	return Anonymization::hideIfNeeded($this->_get('siret'));
    }



    /**** FONCTIONS A RETIRER APRES LE MERGE ****/


      public function isSameCompteThanSociete() {

        return ($this->compte == $this->getSociete()->compte_societe);
    }

    /**** FIN FONCTIONS A RETIRER APRES LE MERGE ****/

    public function getNumeroCourt() {

        return str_replace(str_replace('SOCIETE-', '', $this->id_societe), '', $this->identifiant);
    }

    public function setStatut($s) {
        $r = $this->_set('statut', $s);
        $this->updateLiaisonsOpposees();
        return $r;
    }

    public function getStatutLibelle(){
      return CompteClient::$statutsLibelles[$this->getStatut()];
    }

    public function getRaisonSociale() {
        return Anonymization::hideIfNeeded($this->_get('raison_sociale'));
    }

    public function getNom() {
        return Anonymization::hideIfNeeded($this->_get('nom'));
    }

    public function getAdresse() {
        return Anonymization::hideIfNeeded($this->_get('adresse'));
    }
    public function getAdresseComplementaire() {
        return Anonymization::hideIfNeeded($this->_get('adresse_complementaire'));
    }

    public function getMeAndLiaisonOfType($type) {
        return array_merge(array($this), $this->getLiaisonObjectOfType($type));
    }

    public function  getLiaisonObjectOfType($type) {
        $etablissements = array();
        foreach ($this->getLiaisonOfType($type) as $o) {
            $e = EtablissementClient::getInstance()->find($o->id_etablissement);
            if ($e && ($e->cvi || $e->ppm)) {
                $etablissements[] = $e;
            }
        }
        return $etablissements;
    }

    public function  getLiaisonOfType($type) {
        $liaisons = array();
        if ($this->exist('liaisons_operateurs')) {
            foreach ($this->liaisons_operateurs as $k => $o) {
                if ($o->type_liaison == $type) {
                    $liaisons[] = $o;
                }
            }
        }
        return $liaisons;
    }

    public function hasCooperateur($cvi)
    {
        if (! $this->hasFamille(EtablissementFamilles::FAMILLE_COOPERATIVE)) {
            return false;
        }

        $cooperateurs = $this->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR);

        $cooperateurs = array_map(function ($cooperateur) {
            return is_object($cooperateur) ? $cooperateur->cvi : $cooperateur['cvi'];
        }, $cooperateurs);

        return in_array($cvi, $cooperateurs);
    }

    public function getLaboLibelle() {
        $labos = $this->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_LABO);
        if (!count($labos)) {
            return null;
        }
        return $labos[0]->libelle_etablissement;
    }

    public function getLiaisonsOperateursSorted() {
        $liaisonsOperateurs = $this->liaisons_operateurs->toArray();

        uasort($liaisonsOperateurs, function($a, $b) {
            return $a->libelle_etablissement > $b->libelle_etablissement;
        });

        return $liaisonsOperateurs;
    }

    public function getAllChais() {
        $chais = [];
        if (!$this->exist('chais')) return $chais;
        foreach ($this->chais as $key => $chai) {
            $chais[$key] = (string)$chai;
        }
        return $chais;
    }

    public function getChai($key) {
        if (!$this->exist('chais')) return null;
        if (!is_numeric($key)) return null;
        if (!$this->chais->exist($key)) return null;
        return $this->chais->get($key);
    }

    public function getKeyChai($infos) {
        if (!$this->exist('chais')) return null;
        foreach ($this->chais as $key => $chai) {
            if ($infos->nom == $chai->nom &&
    			$infos->adresse == $chai->adresse &&
    			$infos->commune == $chai->commune &&
                $infos->code_postal = $chai->code_postal
            ) return $key;
        }
        return null;
    }

}
