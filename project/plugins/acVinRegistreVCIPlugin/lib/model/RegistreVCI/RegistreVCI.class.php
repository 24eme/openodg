<?php
/**
 * Model for RegistreVCI
 *
 */

class RegistreVCI extends BaseRegistreVCI implements InterfaceProduitsDocument {

      public function constructId() {
          $id = RegistreVCIClient::TYPE_COUCHDB.'-' . $this->identifiant . '-' . $this->campagne;
          if($this->version) {
              $id .= "-".$this->version;
          }
          $this->set('_id', $id);
      }

      public function getConfiguration() {

          return ConfigurationClient::getInstance()->getConfiguration($this->campagne);
      }

      public function initDoc($identifiant, $campagne) {
          $this->identifiant = $identifiant;
          $this->campagne = $campagne;
          $etablissement = $this->getEtablissementObject();
      }

      public function getProduits() {
        return array();
      }

      public function getConfigProduits() {

          return $this->getConfiguration()->declaration->getProduits();
      }

      public function getEtablissementObject() {
        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
      }

      public function addMouvement($produit, string $mouvement_type, int $volume, string $lieu) {
          $hproduit = preg_replace('/\/*declaration\//', '', $produit->getHash());
          $nDetail = $this->add('declaration')->add($hproduit)->addMouvement($mouvement_type, $volume, $lieu);
          $mvt = $this->add('mouvements')->add();
          $mvt->produit_hash = $hproduit;
          $mvt->produit_libelle = $produit->getLibelleComplet();
          $mvt->detail_hash = $nDetail->stockage_identifiant;
          $mvt->detail_libelle = $nDetail->getLibelle();
          $mvt->volume = $volume;
          $mvt->mouvement_type = $mouvement_type;
          $mvt->stock_resultant = $nDetail->stock_final;
          if (RegistreVCIClient::MOUVEMENT_SENS($mouvement_type) > 0) {
            $mvt->date = $this->campagne.'-10-15';
          }else{
            $mvt->date = ($this->campagne + 1).'-12-31';
          }
      }

}
