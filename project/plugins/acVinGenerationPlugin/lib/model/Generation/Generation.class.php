<?php
/**
 * Model for Generation
 *
 */

class Generation extends BaseGeneration {

  public function constructId() {
      if(!$this->date_emission) {
          $this->setDateEmission(date('YmdHis'));
      }
    $this->setIdentifiant($this->type_document.'-'.$this->date_emission);
    $this->set_id('GENERATION-'.$this->identifiant);
    if(!$this->statut) {
        $this->setStatut(GenerationClient::GENERATION_STATUT_ENATTENTE);
    }

    if(count(GenerationConfiguration::getInstance()->getSousGeneration($this->type_document))) {
        $this->add('sous_generation_types', GenerationConfiguration::getInstance()->getSousGeneration($this->type_document));
    }
  }

  public function save() {
    $this->nb_documents = count($this->documents);
    if (!$this->nb_documents && $this->statut == GenerationClient::GENERATION_STATUT_GENERE) {
	   $this->nb_documents = count($this->fichiers);
    }

    $this->setDateMaj(date('YmdHis'));
    return parent::save();
  }

  public function setStatut($statut) {
    if($statut == GenerationClient::GENERATION_STATUT_ENATTENTE) {
      $this->message = "";
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENCOURS) {
      $this->message = "";
    }

    if($statut == GenerationClient::GENERATION_STATUT_ENERREUR) {
      $this->message = "";
    }

    return $this->_set('statut', $statut);
  }

  public function getOrCreateSubGeneration($typeDocument) {
      $subGeneration = GenerationClient::getInstance()->find($this->_id."-".$typeDocument);

      if($subGeneration) {
          return $subGeneration;
      }

      $subGeneration = new Generation();
      $subGeneration->_id = $this->_id."-".$typeDocument;
      $subGeneration->type_document = $typeDocument;
      $subGeneration->date_emission = date('YmdHis');
      $subGeneration->statut = GenerationClient::GENERATION_STATUT_ENATTENTE;

      return $subGeneration;
  }

  public function getMasterGeneration() {

      return GenerationClient::getInstance()->find(preg_replace("/-[^-]*$/", "", $this->_id));
  }

  public function getSubGenerations() {

      return GenerationClient::getInstance()->findSubGeneration($this->_id);
  }

  public function reload() {
      if ($this->type_document != GenerationClient::TYPE_DOCUMENT_FACTURES_MAILS) {
          $this->remove('fichiers');
          $this->add('fichiers');
      }
      if(count($this->arguments) > 0) {
          $this->add('pregeneration_needed', 1);
      }
      $this->statut = GenerationClient::GENERATION_STATUT_ENATTENTE;
  }

  public function regenerate() {
      $this->somme = 0;
      $documents = array_merge($this->documents->toArray(true, false), $this->add('documents_regenerate')->toArray(true, false));
      $this->add('documents_regenerate', $documents);
      $this->remove('documents');
      $this->add('documents');
      $this->reload();
  }

  public function __toString() {
     return GenerationClient::getInstance()->getDateFromIdGeneration($this->_id);
  }

  public function getSomme() {
      return Anonymization::hideIfNeeded($this->_get('somme'));
  }

    public function getDate() {
        if (!$this->exist('arguments') || !$this->arguments->exist('date_mouvement')) {
            return $this->date_emission;
        }
        if (strpos($this->arguments->date_mouvement, '/')) {
            $d = explode('/', $this->arguments->date_mouvement);
            return sprintf('%4d-%02d-02d', $d[2], $d[1], $d[0]);
        }
        return $this->arguments->date_mouvement;
    }

    public function getPeriode() {
        $d = explode('-', $this->getDate());
        if ($d[1] < 8) {
            return $d[0] - 1;
        }
        return $d[0];
    }

}
