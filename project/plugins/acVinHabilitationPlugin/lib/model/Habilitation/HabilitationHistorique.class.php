<?php
/**
 * Model for HabilitationHistorique
 *
 */

class HabilitationHistorique extends BaseHabilitationHistorique {

  const ADD_PRODUIT = "ADD_PRODUIT";
  const CHANGE_STATUT = "CHANGE_STATUT";


  public static $actionsDescriptions = array(self::ADD_PRODUIT => "Ajout du produit", self::CHANGE_STATUT => "");

}
