<?php
/**
 * Model for TravauxMarcFournisseur
 *
 */

class TravauxMarcFournisseur extends BaseTravauxMarcFournisseur {

    public function setEtablissementId($etablissement_id) {
        $this->_set('etablissement_id', $etablissement_id);

        $etablissement =  $this->getEtablissement();
        $this->nom = null;
        if($etablissement) {
            $this->nom = CompteClient::getInstance()->makeLibelle($etablissement->getCompte());
        }
    }

    public function getDateLivraisonFr() {
        $date = $this->getDateLivraisonObject();

        if (!$date) {

            return null;
        }

        return $date->format('d/m/Y');
    }

    public function getDateLivraisonObject() {
        if (!$this->date_livraison) {

            return null;
        }

        return new DateTime($this->date_livraison);
    }

    public function getEtablissement() {
        if(!$this->etablissement_id) {
            return null;
        }

        return EtablissementClient::getInstance()->find($this->etablissement_id);
    }
}
