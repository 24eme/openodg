<?php

abstract class MouvementFactures extends acCouchdbDocumentTree implements InterfaceMouvementFactures
{

    protected $origines = array();

    public function createFromCotisationAndDoc($cotisation,$doc) {
        $this->fillFromCotisation($cotisation);
        $this->replaceLibelle(['detail_libelle', 'type_libelle', 'categorie', 'type_hash'], '%millesime%', $doc->getPeriode());
        $this->facture = 0;
        $this->facturable = 1;
        if($doc->exist('version')) {
            $this->version = $doc->version;
        }
        if($doc->exist('validation') && $doc->validation) {
            $this->date = $doc->validation;
            $this->date_version = $doc->validation;
        }
        if($doc->exist('date_commission') && $doc->date_commission) {
            $this->date = $doc->date_commission;
        }

        if($cotisation->getConfigDate()) {
            $this->date = str_replace("%periode+1%", $doc->getPeriode()+1, $cotisation->getConfigDate());
        }

        $this->type = $doc->type;
        if($doc->exist('campagne')) {
            $this->campagne = $doc->campagne;
        }

        if ($cotisation->getConfigCollection()->getDocument()->exist('region')) {
            $this->add('region', $cotisation->getConfigCollection()->getDocument()->getRegion());
        }
    }

    public function fillFromCotisation($cotisation) {
        $this->categorie = str_replace("%detail_identifiant%", $this->detail_identifiant, $cotisation->getCollectionKey());
        $this->type_hash = $cotisation->getDetailKey();
        $this->type_libelle = str_replace("%detail_identifiant%", $this->detail_identifiant, $cotisation->getConfigCollection()->libelle);
        $this->detail_libelle = str_replace("%detail_identifiant%", $this->detail_identifiant, $cotisation->getConfigLibelle());
        $this->quantite = $cotisation->getQuantite();
        $this->taux = $cotisation->getPrix();
        $this->tva = $cotisation->getTva();
        if($cotisation->getUnite()) {
            $this->add('unite', $cotisation->getUnite());
        }
    }

    public function replaceLibelle($cles, $to_replace, $by)
    {
        foreach ($cles as &$cle) {
            $this->$cle = str_replace($to_replace, $by, $this->$cle);
        }
    }

    public function setProduitHash($value) {
        $this->_set('produit_hash',  $value);
        if(!$this->produit_libelle && $this->exist('denomination_complementaire')) {
            $this->produit_libelle = $this->getProduitConfig()->getLibelleFormat($this->denomination_complementaire, "%format_libelle%");
        } elseif(!$this->produit_libelle && !$this->exist('denomination_complementaire')) {
            $this->produit_libelle = $this->getProduitConfig()->getLibelleFormat(array(), "%format_libelle%");
        }
    }

    public function facturer() {
        if($this->isFacturable()) {
            $this->facture = 1;
        }
    }

    public function defacturer() {
        $this->facture = 0;
    }

    public function getMD5Key() {
        $key  = $this->getDocument()->_id . $this->getDocument()->getIdentifiant();
        $key .= $this->produit_hash . $this->categorie . $this->type_hash . $this->type_libelle;
        $key .= $this->detail_identifiant.$this->date;
        if ($this->exist('region')) {
            $key.= $this->region;
        }
        $key .= $this->getDocument()->validation_odg;

        return md5($key);
    }

    public function isFacturable() {
        return $this->facturable;
    }

    public function isFacture() {
        if (!$this->isFacturable()) {

            return true;
        }

        return (bool) $this->facture;
    }

    public function isNonFacture() {

        if (!$this->isFacturable()) {

            return true;
        }

        return !$this->facture;
    }

    public function setVolume($v) {
      if ($this->volume === 0 && $v) {
	throw new sfException('PB Facturable : plus capable de savoir si le mouvement est facturable ou non');
      }
      if (!$v)
	$this->facturable = 0;
      return $this->_set('volume', $v);
    }

/**
*   TODO : A partir d'ici les fonctions semblent servir pour Giilda => beaucoup de code à nettoyer
*/


    public function isVrac() {

        return $this->vrac_numero;
    }

    public function getVrac() {

        if (!$this->isVrac()) {
            return null;
        }

        $vrac = VracClient::getInstance()->findByNumContrat($this->vrac_numero);

        if (!$vrac) {

            throw new sfException(sprintf("Le contrat '%s' n'a pas été trouvé", $this->vrac_numero));
        }

        return $vrac;
    }

    public function getProduitConfig() {

       return ConfigurationClient::getCurrent()->get($this->produit_hash);
    }

    public function getDocId() {

        return $this->getDocument()->_id;
    }

    public function getId() {

        return $this->getDocument()->_id.'/mouvements/'.$this->getKey();
    }

    public function getType() {

        return $this->getDocument()->getType();
    }

    public function getNumeroArchive() {
        if(!$this->isVrac()) {
            return;
        }

        return $this->detail_libelle;
    }

    public function getOrigines() {

        return $this->origines;
    }

    public function setOrigines($origines) {

        $this->origines = $origines;
    }
}
