<?php

/**
 * Model for Compte
 *
 */
class Compte extends BaseCompte implements InterfaceArchivageDocument {
	
    const CAMPAGNE_ARCHIVE = 'UNIQUE';

    protected $archivage_document = null;

    public function __construct($type_compte = null) {
        parent::__construct();
        $this->setTypeCompte($type_compte);
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->archivage_document = new ArchivageDocument($this, "%06d");
    }

    public function constructId() {
        $this->set('_id', 'COMPTE-' . $this->identifiant);
    }

    public function getCampagneArchive() {
        if(!$this->_get('campagne_archive')) {
            $this->_set('campagne_archive', self::CAMPAGNE_ARCHIVE);
        }
        return $this->_get('campagne_archive');
    }

    public function save($synchro_etablissement = true, $update_coodronnees = false) {
        if ($this->isNew() && !$this->identifiant) {
            $this->identifiant = CompteClient::getInstance()->createIdentifiantForCompte($this);
            $this->statut = CompteClient::STATUT_ACTIF;
        }
        
        if ($this->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT) && $synchro_etablissement) {
            $this->updateChais();
            $etablissement = EtablissementClient::getInstance()->createOrFind($this->cvi);
            if ($this->isNew() && !$etablissement->isNew()) {
                throw new sfException("Pas possible de créer un etablissement avec cet Id (".$this->cvi.")");
            }
            $etablissement->synchroFromCompte($this);
            $etablissement->save();
            $this->setEtablissement($etablissement->_id);
        }

        $this->updateNomAAfficher();
        $this->updateInfosTagsAutomatiques();
        $this->updateTags();
        if($update_coodronnees) {
            $this->updateCoordonneesLongLat();
        }

        parent::save();
    }

    protected function preSave() {
        $this->archivage_document->preSave();
        $this->identifiant_interne = $this->numero_archive;
    }

    public function updateNomAAfficher() {
        $this->nom_a_afficher = "";

        if ($this->nom || $this->prenom) {
            $this->nom_a_afficher = trim(sprintf("%s %s %s", $this->civilite, $this->prenom, $this->nom));
        }

        if ($this->raison_sociale && $this->nom_a_afficher) {
            $this->nom_a_afficher .= " - ";
        }

        if ($this->raison_sociale) {
            $this->nom_a_afficher .= $this->raison_sociale;
        }
    }

    public function setSiret($value) {
        $return = $this->_set('siret', $value);

        if($value && strlen($value) >= 9)  {
            $this->siren = substr($value, 0, 9);
        }

        return $return;
    }

    public function getInfosAttributs() {
        return $this->infos->get('attributs');
    }

    public function getInfosProduits() {
        return $this->infos->get('produits');
    }

    public function getInfosManuels() {
        return $this->infos->get('manuels');
    }

    public function getInfosAutomatiques() {
        return $this->infos->get('automatiques');
    }
    
    public function getInfosSyndicats() {
        return $this->infos->get('syndicats');
    }

    public function hasProduits() {
        return count($this->infos->get('produits'));
    }
    
    public function hasAttributs() {
        return count($this->infos->get('attributs'));
    }

    public function hasManuels() {
        return count($this->infos->get('manuels'));
    }
    
    public function hasSyndicats() {
        return count($this->infos->get('syndicats'));
    }

    public function hasAutomatiques() {
        return count($this->infos->get('automatiques'));
    }

    public function getDefaultManuelsTagsFormatted() {
        $result = '[';
        foreach ($this->getInfosManuels() as $infosManuels) {
            $result.='"' . $infosManuels . '",';
        }
        if (count($this->getInfosManuels())) {
            $result = substr($result, 0, strlen($result) - 1);
        }
        $result.=']';
        return $result;
    }

    public function removeInfosTagsNode($node) {
        if ($this->exist('infos') && $this->infos->exist($node)) {
            $this->infos->remove($node);
            $this->infos->add($node);
        }
    }
    
    public function updateInfosTagsAttributs($attributs_array = array()) {
        $this->removeInfosTagsNode('attributs');
        foreach ($attributs_array as $attribut_code) {
            $this->updateInfosTags('attributs', $attribut_code, CompteClient::getInstance()->getAttributLibelle($attribut_code));
        }
    }
    
    public function updateInfosTagsManuels($infos_manuels = array()) {
        $this->removeInfosTagsNode('manuels');
        foreach ($infos_manuels as $info_manuel) {
            $info_manuel_key = str_replace(' ', '_', $info_manuel);
            $this->updateInfosTags('manuels', $info_manuel_key, $info_manuel);
        }
    }

    public function updateLocalTagsProduits($produits_hash_array = array()) {
        //$this->removeInfosTagsNode('produits');
        $allProduits = ConfigurationClient::getConfiguration()->getProduits();
        foreach ($produits_hash_array as $produits_hash) {
            $libelle_complet = $allProduits[str_replace('-', '/', $produits_hash)]->getLibelleComplet();
            $this->updateInfosTags('produits', $produits_hash, $libelle_complet);
        }
    }

    public function updateLocalSyndicats($syndicats_array = array()) {
         $this->removeInfosTagsNode('syndicats');
        foreach ($syndicats_array as $syndicatId) {
            $syndicat = CompteClient::getInstance()->find($syndicatId);
            $syndicat_libelle = $syndicat->nom_a_afficher;//." (".$syndicat->commune.")";
            $this->updateInfosTags('syndicats', $syndicatId, $syndicat_libelle);
        }
    }
    
    public function updateInfosTags($nodeType, $key, $value) {        
        if (!$this->infos->exist($nodeType)) {
            $this->infos->add($nodeType, null);
        }
        $this->infos->$nodeType->add($key, $value);
    }
    
    public function getEtablissementObj() {
        if(!$this->getEtablissement()){
            return null;
        }
        return EtablissementClient::getInstance()->find($this->getEtablissement());
    }

    public function isTypeCompte($type) {
        return $type == $this->getTypeCompte();
    }

    public function updateInfosTagsAutomatiques() {
        $this->updateInfosTags('automatiques', "TYPE_COMPTE_LIBELLE",  CompteClient::getInstance()->getCompteTypeLibelle($this->getTypeCompte()));
    }

    public function updateTags() {
        if ($this->exist('tags')) {
            $this->remove('tags');
        }
        $this->add('tags');
        foreach ($this->getInfosAttributs() as $key => $attribut) {
            $this->addTag('attributs', $this->formatTag($attribut));
        }
        foreach ($this->getInfosProduits() as $produit) {
            $this->addTag('produits', $this->formatTag($produit));
        }
        foreach ($this->getInfosManuels() as $key => $manuel) {
            $this->addTag('manuels', $this->formatTag($manuel));
        }
        foreach ($this->getInfosAutomatiques() as $automatique) {
            $this->addTag('automatiques', $this->formatTag($automatique));
        }
        foreach ($this->getInfosSyndicats() as $syndicat) {
            $this->addTag('syndicats', $this->formatTag($syndicat));
        }
    }

    private function formatTag($tag) {
        return $tag;
    }

    public function addTag($nodeType, $value) {
        if (!$this->tags->exist($nodeType)) {
            $this->tags->add($nodeType, null);
        }
        $this->tags->$nodeType->add(null, $value);
    }

    public function calculCoordonnees($adresse, $commune, $code_postal) {
        $adresse = trim(preg_replace("/B[\.]*P[\.]* [0-9]+/", "", $adresse));

        $url = sfConfig::get('app_osm_url_search').'?q='.urlencode($adresse." ".$commune." ".$code_postal);
        
        $file = file_get_contents($url);

        $result = json_decode($file);

        if(!count($result)){
            return false;
        }

        if(KeyInflector::slugify($result->response->docs[0]->commune) != KeyInflector::slugify($commune)) {
            echo sprintf("WARNING;Commune différent %s / %s;%s\n", $result->response->docs[0]->commune, $commune, $this->_id);
        }

        return array("lat" => $result->response->docs[0]->lat, "lon" => $result->response->docs[0]->lng);
    }

    public function updateCoordonneesLongLatByNoeud($noeud) {
        $coordonnees = $this->calculCoordonnees($noeud->adresse, $noeud->commune, $noeud->code_postal);

        if(!$coordonnees) {

            return false;
        }

        $noeud->lon = $coordonnees["lon"];
        $noeud->lat = $coordonnees["lat"];

        return true;
    }

    public function updateCoordonneesLongLat() {
        $this->updateCoordonneesLongLatByNoeud($this);

        foreach($this->chais as $chai) {
            if($chai->adresse == $this->adresse && $chai->commune == $this->commune) {
                $chai->lon = $this->lon;
                $chai->lat = $this->lat;
                continue;
            }

            $this->updateCoordonneesLongLatByNoeud($chai);
        }

        return true;
    }

    public function updateChais() {
        $newChais = array();
        foreach ($this->chais as $chai) {
            if($chai->adresse && $chai->commune && $chai->code_postal){
                $newChai = $chai->toArray(false, false);
                $newChai['attributs'] = array();
                foreach($chai->attributs as $key => $libelle) {
                    $newChai['attributs'][$key] = CompteClient::getInstance()->getChaiAttributLibelle($key);
                }
                $newChais[] = $newChai;
            }
            
        }
        $this->remove("chais");
        $this->add("chais", $newChais);
    }

    public function getCoordonneesLatLon() {

        $points = array();

        if($this->lat && $this->lon) {
            $points[$this->lat.$this->lon] = array($this->lat, $this->lon);
        }

        foreach($this->chais as $chai) {
            if(!$chai->lat && $chai->lon) {
                continue;
            }
            $points[$chai->lat.$chai->lon] = array($chai->lat, $chai->lon);
        }

        return $points;
    }

    public function findChai($adresse, $commune, $code_postal) {
        foreach($this->chais as $chai) {
            if(KeyInflector::slugify(str_replace(" ", "", $chai->adresse.$chai->commune.$chai->code_postal)) != KeyInflector::slugify(str_replace(" ", "", $adresse.$commune.$code_postal))) {

                continue;
            }

            return $chai;
        }

        return null;
    }

    public function archiver() {
        $this->statut = CompteClient::STATUT_INACTIF;
        $this->date_archivage = date('Y-m-d');
    }

    public function desarchiver() {
        $this->statut = CompteClient::STATUT_ACTIF;
        $this->date_archivage = null;
    }
    
    public function getRegionViticole() {
    	return CompteClient::REGION_VITICOLE;
    }

    public function getSyndicatsViticole() {
    	$result = array();
    	if ($syndicats = $this->infos->syndicats) {
    		$result = array_keys($syndicats->toArray());
    	}
    	return $result;
    	
    }

    public function isAdherentSyndicat() {

        return $this->infos->exist('attributs') && $this->infos->attributs->exist(CompteClient::ATTRIBUT_ETABLISSEMENT_ADHERENT_SYNDICAT);
    }

    /*** ARCHIVAGE ***/

    public function getNumeroArchive() {

        return $this->_get('numero_archive');
    }

    public function isArchivageCanBeSet() {

        return true;
    }

    /*** FIN ARCHIVAGE ***/

}
