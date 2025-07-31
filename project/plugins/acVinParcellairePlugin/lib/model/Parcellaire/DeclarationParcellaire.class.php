<?php

class DeclarationParcellaire extends acCouchdbDocument {

    public function getParcelleFromParcellaire($id) {
        $parcellaire = $this->getParcellaire();

        if(!$parcellaire) {

            return null;
        }

        return $parcellaire->getParcelleFromParcellaireId($id);
    }

    public function getParcelles($hashproduitFilter = null) {
        $parcelles = [];
        if ($this->declaration && count($this->declaration)) foreach ($this->declaration->getParcelles($hashproduitFilter) as $p) {
            if (isset($parcelles[$p->getParcelleKeyId()])) {
                throw new sfException('parcelleid '.$p->getParcelleKeyId().' already exists');
            }
            $parcelles[$p->getParcelleKeyId()] = $p;
        }
        return $parcelles;
    }

    public function getParcellesByCommunes() {
        $parcelles = [];
        foreach ($this->getParcelles() as $p) {
            $c = $p->commune;
            if (!isset($parcelles[$c])) {
                $parcelles[$c]= [];
            }
            $parcelles[$c][] = $p;
        }
        return $parcelles;
    }

    protected $parcellaire = null;
    protected $parcellaire_origine = null;

    public function getParcellaire() {
        if (!$this->parcellaire) {
            $cm = new CampagneManager('08-01');
            $date = ($this->periode + 1).'-07-31';
            if ($this->exist('date')) {
                $date = $this->date;
            }
            $date_end = $cm->getDateFinByDate($date);
            $this->parcellaire = ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $date_end);
            $this->parcellaire_origine = ($this->parcellaire) ? $this->parcellaire->_id : null;
        }
        return $this->parcellaire;
    }

    public function getParcellaireAffectation() {
        return ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, ($this->periode + 1).'-07-31');
    }

    protected $parcelles_idu = null;

    public function getParcellesByIdu() {
        if(is_array($this->parcelles_idu)) {

            return $this->parcelles_idu;
        }

        $this->parcelles_idu = [];

        foreach($this->getParcelles() as $parcelle) {
            $this->parcelles_idu[$parcelle->idu][] = $parcelle;
        }

        return $this->parcelles_idu;
    }

    public function getParcellaire2Reference() {
        $parcellaire = $this->getParcellaire();
        if (ParcellaireConfiguration::getInstance()->isParcellesFromAffectationparcellaire() && !in_array($this->type, [ParcellaireAffectationClient::TYPE_MODEL, ParcellaireIntentionAffectationClient::TYPE_MODEL])) {
            $parcellaire = $this->getParcellaireAffectation();
        }
        return $parcellaire;
    }


    public function getParcellesFromReference() {
        $parcellaireCurrent = $this->getParcellaire2Reference();
        if (!$parcellaireCurrent) {
          $parcellaireCurrent = $this->getParcellaire();
        }
        if (!$parcellaireCurrent) {
            return [];
        }
        return $parcellaireCurrent->getDeclarationParcelles();
    }

    public function getDeclarationParcelles() {
        $parcelles = [];
        foreach($this->declaration->getParcelles() as $k => $p) {
            $parcelles[$p->getParcelleId()] = $p;
        }
        return $parcelles;
    }

    public function getParcelleFromParcelleReference($p) {
        $parcelles = $this->getDeclarationParcelles();
        if (!isset($parcelles[$p->getParcelleId()])) {
            return null;
        }
        return $parcelles[$p->getParcelleId()];
    }

    public function setParcellesFromParcellaire(array $hashes) {
        $parcelles_orig = $this->getDeclarationParcelles();

        $this->remove('declaration');
        $this->add('declaration');

      	$parcelles = $this->getParcellesFromReference();
        if (!$parcelles || !count($parcelles)) {
            throw new sfException('pas de parcelles du parcellaire');
        }
        foreach($hashes as $h) {
            $pid = $h;
            if (strpos($h, 'detail') !== false ){
                $t = explode('/detail/', str_replace('/declaration/', '', $h));
                $pid = $t[1];
            }
            if (!$parcelles[$pid]) {
                throw new sfException('parcelle '.$pid.' not found');
                continue;
            }
            $p_orig = $parcelles[$pid];
            if (!$p_orig->produit_hash) {
                throw new sfException('To affect parcelle '.$pid.' produit_hash is needed');
            }
            $p = $this->declaration->add(str_replace('/declaration/', '', $p_orig->produit_hash));
            if (isset($parcelles_orig[$pid])) {
                $d = $p->detail->add($pid, $parcelles_orig[$pid]);
            }else{
                $d = $p->detail->add($pid);
            }
            if (! $p->libelle) {
                $p->libelle = $p_orig->getProduit()->getLibelle();
            }
            ParcellaireClient::CopyParcelle($d, $p_orig, true);
            if ($d->exist('active')) {
                $d->active = 1;
            }
            if ($d->exist('affectee')) {
                $d->affectee = 1;
            }
        }
    }

    public function getParcelleById($id) {
        $p = $this->getParcelles();

        if(!isset($p[$id])) {
            return null;
        }
        return $p[$id];
    }

    public function findProduitParcelleByParcelleId($parcelle) {
        $hash = str_replace('/declaration/', '', $parcelle->produit_hash);
        if (!$this->declaration->exist($hash)) {
            return null;
        }
        if (!$this->declaration->get($hash)->detail->exist($parcelle->getParcelleId())) {
            return null;
        }

        $p = $this->declaration->get($hash)->detail->get($parcelle->getParcelleId());

        if($p && $p->cepage == $parcelle->cepage && $p->campagne_plantation == $parcelle->campagne_plantation) {
            return $p;
        }

        return null;
    }

    public function findParcelle($parcelle, $scoreMin = 1, $with_cepage_match = false, &$allready_selected = null) {

        return ParcellaireClient::findParcelle($this, $parcelle, $scoreMin, $with_cepage_match, $allready_selected);
    }

    private $idunumbers = null;
    public function getNbUDIAlreadySeen($idu) {
        if (!$this->idunumbers) {
            $this->idunumbers = [];
        }
        if (!isset($this->idunumbers[$idu])) {
            $this->idunumbers[$idu] = 0;
        }
        return $this->idunumbers[$idu]++;
    }

}
