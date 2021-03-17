<?php
class MandatSepaConfiguration implements InterfaceMandatSepaPartie {

  protected static $_instance;
  protected $configuration;


  public static function getInstance() {
      if ( ! isset(self::$_instance)) {
          self::$_instance = new self();
      }
      return self::$_instance;
  }
  public function __construct() {
      if(!sfConfig::has('mandatsepa_configuration')) {
		      throw new sfException("La configuration pour les mandats SEPA n'a pas été définie pour cette application");
	    }
      $this->configuration = sfConfig::get('mandatsepa_configuration', array());
  }

  public function isActive() {
      if(!isset($this->configuration['is_active'])){
        return false;
      }
      return $this->configuration['is_active'];
  }

  public function getFrequencePrelevement() {
      if(!isset($this->configuration['frequence_prelevement'])){
        return "";
      }
      return $this->configuration['frequence_prelevement'];
  }

  public function getMentionAutorisation() {
      if(!isset($this->configuration['mention_autorisation'])){
        return "";
      }
      return $this->configuration['mention_autorisation'];
  }

  public function getMentionRemboursement() {
      if(!isset($this->configuration['mention_remboursement'])){
        return "";
      }
      return $this->configuration['mention_remboursement'];
  }

  public function getMentionDroits() {
      if(!isset($this->configuration['mention_droits'])){
        return "";
      }
      return $this->configuration['mention_droits'];
  }

  public function getMandatSepaIdentifiant() {
      if(!isset($this->configuration['creancier'])){
        return "";
      }
      if(!isset($this->configuration['creancier']['identifiant_ics'])){
        return "";
      }
      return $this->configuration['creancier']['identifiant_ics'];
  }
  public function getMandatSepaNom() {
      if(!isset($this->configuration['creancier'])){
        return "";
      }
      if(!isset($this->configuration['creancier']['nom'])){
        return "";
      }
      return $this->configuration['creancier']['nom'];
  }
  public function getMandatSepaAdresse() {
      if(!isset($this->configuration['creancier'])){
        return "";
      }
      if(!isset($this->configuration['creancier']['adresse'])){
        return "";
      }
      return $this->configuration['creancier']['adresse'];
  }
  public function getMandatSepaCodePostal() {
      if(!isset($this->configuration['creancier'])){
        return "";
      }
      if(!isset($this->configuration['creancier']['code_postal'])){
        return "";
      }
      return $this->configuration['creancier']['code_postal'];
  }
  public function getMandatSepaCommune() {
      if(!isset($this->configuration['creancier'])){
        return "";
      }
      if(!isset($this->configuration['creancier']['commune'])){
        return "";
      }
      return $this->configuration['creancier']['commune'];
  }
}
