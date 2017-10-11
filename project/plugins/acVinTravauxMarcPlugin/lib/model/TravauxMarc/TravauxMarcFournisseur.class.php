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
            $this->nom = $etablissement->nom;
        }
    }

    public function getEtablissement() {
        if(!$this->etablissement_id) {
            return null;
        }
        
        return EtablissementClient::getInstance()->find($this->etablissement_id);
    }
}
