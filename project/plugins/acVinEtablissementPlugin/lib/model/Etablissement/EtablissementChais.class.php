<?php
/**
 * Model for EtablissementLieuStockage
 *
 */

class EtablissementChais extends BaseEtablissementChais {
    private $autocalcule = true;
    public function disableAutocalcule() {
      $this->autocalcule = false;
    }

    public static function getIdentifiantEtablissementPart($identifiant) {

        return preg_replace('/([0-9]+)C.*$/', '\1', $identifiant);
    }

    public static function getIdentifiantChaiPart($identifiant) {
        $identifiant = preg_replace('/^.*[0-9]+C?/', '', $identifiant);

        if(!$identifiant) {

            return null;
        }

        return intval($identifiant);
    }

    public function isSameAdresseThanEtablissement() {
      return !($this->_get('nom') || $this->_get('adresse') || $this->_get('code_postal') || $this->_get('commune'));
    }

    public function getNom() {
      if (!$this->autocalcule || !$this->isSameAdresseThanEtablissement()) {
        return $this->_get('nom');
      }
      return $this->getDocument()->nom;
    }
    public function getAdresse() {
      if (!$this->autocalcule|| !$this->isSameAdresseThanEtablissement()) {
        return $this->_get('adresse');
      }
      return $this->getDocument()->adresse;
    }
    public function getCommune() {
      if (!$this->autocalcule|| !$this->isSameAdresseThanEtablissement()) {
        return $this->_get('commune');
      }
      return $this->getDocument()->commune;
    }
    public function getCodePostal() {
      if (!$this->autocalcule || !$this->isSameAdresseThanEtablissement()) {
        return $this->_get('code_postal');
      }
      return $this->getDocument()->code_postal;
    }

}
