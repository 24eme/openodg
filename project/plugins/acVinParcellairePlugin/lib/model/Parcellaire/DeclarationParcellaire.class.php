<?php

class DeclarationParcellaire extends acCouchdbDocument {

    public function getParcelles() {
        $parcelles = [];
        foreach ($this->declaration->getParcelles() as $p) {
            $parcelles[$p->getParcelleId()] = $p;
        }
        return $parcelles;
    }

    public function getParcellesByCommunes() {
        $parcelles = [];
        foreach ($this->declaration->getParcelles() as $p) {
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
        return $this->getParcellaire();
    }

    public function getParcellesFromParcellaire() {
        $parcellaireCurrent = $this->getParcellaire2Reference();
        if (!$parcellaireCurrent) {
          $parcellaireCurrent = $this->getParcellaire();
        }
        if (!$parcellaireCurrent) {
            return [];
        }
        return $parcellaireCurrent->getParcelles();
    }

    public function getParcelleFromParcelleParcellaire($p) {
        foreach($this->declaration->getParcelles() as $d) {
            if ($p->parcelle_id == $d->parcelle_id) {
                return $d;
            }
        }
        return null;
    }

    public function setParcellesFromParcellaire(array $hashes) {
        $this->remove('declaration');
        $this->add('declaration');

      	$parcelles = $this->getParcellesFromParcellaire();
        if (!$parcelles || !count($parcelles)) {
            throw new sfException('pas de parcelles du parcellaire');
        }
        foreach($hashes as $h) {
            $pid = $h;
            if (strpos($h, 'detail') !== false ){
                $t = explode('/detail/', str_replace('/declaration/', '', $h));
                $pid = $t[1];
            }
            print_r($pid);
            if (!$parcelles[$pid]) {
                throw new sfException('parcelle '.$pid.' not found');
                continue;
            }
            $p_orig = $parcelles[$pid];
            if (!$p_orig->produit_hash) {
                throw new sfException('To affect parcelle '.$pid.' produit_hash is needed');
            }
            $p = $this->declaration->add(str_replace('/declaration/', '', $p_orig->produit_hash));
            $d = $p->detail->add($pid);
            ParcellaireClient::CopyParcelle($d, $p_orig);
            if ($d->exist('active')) {
                $d->active = 1;
            }
            if ($d->exist('affectee')) {
                $d->affectee = 1;
            }
        }
    }

    public function findParcelle($parcelle) {

        return ParcellaireClient::findParcelle($this, $parcelle, 0.75);
    }

}
