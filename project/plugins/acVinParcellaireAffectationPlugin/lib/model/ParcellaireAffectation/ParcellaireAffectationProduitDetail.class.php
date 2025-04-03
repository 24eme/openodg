<?php
/**
 * Model for ParcellaireAffectationProduitDetail
 *
 */

class ParcellaireAffectationProduitDetail extends BaseParcellaireAffectationProduitDetail {


    public function getDgc() {
        if ($this->getParent()->getKey() == 'DEFAUT') {
            return '';
        }
        $communesDenominations = sfConfig::get('app_communes_denominations');
        foreach ($communesDenominations as $dgc => $communes) {
            if (strpos($dgc, $this->getLieuNode()->getHash()) === false) {
                continue;
            }
            if (in_array($this->code_commune, $communes)) {
                return $dgc;
            }
        }
        return null;
    }

    public function getDgcLibelle() {
        if (!$this->getDgc()) {
            return null;
        }
        return $this->getDocument()->getConfiguration()->get($this->getLieuNode()->getHash())->getLibelle();
    }


    public function getDateAffectationFr() {
        if (!$this->date_affectation) {
            return null;
        }
        $date = new DateTime($this->date_affectation);

        return $date->format('d/m/Y');
    }

    public function getSuperficie($destinataireIdentifiant = null) {
        $superficie = $this->_get('superficie');
        if($destinataireIdentifiant) {
            if ($this->exist('destinations/'.$destinataireIdentifiant)) {
                $superficie = $this->get('destinations/'.$destinataireIdentifiant.'/superficie');
            } elseif ($this->exist('destinations')) {
                return null;
            } elseif($destinataireIdentifiant != $this->getDocument()->identifiant) {
                return null;
            }
        }else {
            //Gestion de la transition
            if ($this->exist('superficie_affectation') && $this->_get('superficie_affectation')) {
                $superficie = $this->_get('superficie_affectation');
                $this->set('superficie', $superficie);
            }
        }
        //On préserve les usages antérieurs où la superficie concernées et superficie_affectation
        if ($this->exist('superficie_affectation')) {
            return $this->get('superficie_affectation');
        }
        if (! $this->getParcelleFromParcellaire()) {
            return $superficie;
        }

        $parcellaire_superficie_real = $this->getParcelleFromParcellaire()->superficie;

        if ($this->getSuperficieParcellaire() != $parcellaire_superficie_real) {
            if ($superficie == $this->getSuperficieParcellaire()) {
                $this->set('superficie', $parcellaire_superficie_real);
            }
            $this->_set('superficie_parcellaire', $parcellaire_superficie_real);
        }
        if ($superficie > $this->getSuperficieParcellaire()) {
            $superficie = $this->getSuperficieParcellaire();
            $this->set('superficie', $superficie);
        }

        return $superficie;
    }
    public function getSuperficieParcellaireAffectable() {
        $superficieAffectable = $this->getSuperficieParcellaire() - $this->getSuperficie();

        return $superficieAffectable > 0 ? $superficieAffectable : 0;
    }

    public function isPartielle() {
        if(!$this->superficie) {
            return false;
        }

        return round($this->superficie,4) < round($this->getSuperficieParcellaire(),4);
    }

    public function updateAffectations() {
        if(!$this->exist('destinations')) {
            return;
        }

        $this->superficie = 0;
        foreach($this->destinations as $destination) {
            $this->superficie = $this->_get('superficie') + $destination->superficie;
        }

        $this->affectee = intval(boolval($this->superficie));
    }

    public function isAffectee() {
        $this->updateAffectations();
        return intval(boolval($this->superficie));
    }

    public function getDestinatairesNom() {
        $noms = [];
        if(!$this->exist('destinations')) {
            return $noms;
        }
        foreach($this->destinations as $d) {
            $noms[] = $d->nom;
        }
        return $noms;
    }

    public function desaffecter(Etablissement $etablissement) {
        $destination = $this->add('destinations')->remove($etablissement->identifiant);
        $this->updateAffectations();
    }

    public function affecter($superficie, Etablissement $etablissement) {
        $destination = $this->add('destinations')->add($etablissement->identifiant);
        $destination->identifiant = $etablissement->identifiant;
        $destination->cvi = $etablissement->cvi;
        $destination->superficie = $superficie;
        if($etablissement->identifiant == $this->getDocument()->identifiant) {
            $destination->nom = "Cave particulière";
        } else {
            $destination->nom = $etablissement->nom;
        }
        $this->updateAffectations();
    }
}
