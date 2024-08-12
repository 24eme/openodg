<?php
/**
 * Model for ParcellaireAffectationProduitDetail
 *
 */

class ParcellaireAffectationProduitDetail extends BaseParcellaireAffectationProduitDetail {


    public function getDgc() {
        $communesDenominations = sfConfig::get('app_communes_denominations');
        $dgcFinal = null;
        foreach ($communesDenominations as $dgc => $communes) {
            if (!in_array($this->code_commune, $communes)) {
                continue;
            }
            if (strpos($dgc, $this->getLieuNode()->getKey()) !== false) {

                return $dgc;
            }

            $dgcFinal = $dgc;
        }
        return $dgcFinal;
    }

    public function getDgcLibelle() {
        $dgc = $this->getDgc();

        if(!$dgc) {
            return null;
        }

        return $this->getDocument()->getDgcLibelle($dgc);
    }


    public function getDateAffectationFr() {
        if (!$this->date_affectation) {
            return null;
        }
        $date = new DateTime($this->date_affectation);

        return $date->format('d/m/Y');
    }

    public function getSuperficie($destinataireIdentifiant = null) {
        if($destinataireIdentifiant && $this->exist('destinations/'.$destinataireIdentifiant)) {

            return $this->get('destinations/'.$destinataireIdentifiant.'/superficie');
        } elseif($destinataireIdentifiant && $this->exist('destinations')) {

            return null;
        } elseif($destinataireIdentifiant && $destinataireIdentifiant != $this->getDocument()->identifiant) {
            return null;
        }

        if ($this->exist('superficie_affectation') && $this->_get('superficie_affectation')) {
            return $this->_get('superficie_affectation');
        }

        return $this->_get('superficie');
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
            $nom[] = $d->nom;
        }
        return $nom;
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
