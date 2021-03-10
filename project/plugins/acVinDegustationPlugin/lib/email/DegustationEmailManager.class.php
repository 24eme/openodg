<?php

class DegustationEmailManager extends Email
{
  private static $_instance = null;

  protected $degustation = null;
  protected $etablissement = null;

  public $etablissementLotsConformes = null;
  public $etablissementLotsNonConformes = null;

  protected $subject = null;

  public function __construct(Degustation $degustation, Etablissement $etablissement, $context = null) {
    $this->degustation = $degustation;
    $this->etablissement = $etablissement;
    $lots = $degustation->getLotsByOperateursAndConformites();
    $lotsEtablissement = $lots[$etablissement->identifiant]->lots;
    $this->etablissementLotsConformes = (isset($lotsEtablissement[Lot::STATUT_CONFORME]))? $lotsEtablissement[Lot::STATUT_CONFORME] : array();
    $this->etablissementLotsNonConformes = (isset($lotsEtablissement[Lot::STATUT_NONCONFORME]))? $lotsEtablissement[Lot::STATUT_NONCONFORME] : array();

    parent::__construct($context);
  }

  public static function getInstance($context = null) {
    if (is_null(self::$_instance)) {
      self::$_instance = new DegustationEmailManager($context);
    }
    return self::$_instance;
  }

  protected function getBodyFromPartial($partial, $vars = null) {
    return $this->getAction()->getPartial($partial, $vars);
  }

  public function getMailerLink() {

    $email = $this->etablissement->email;
    $cc = implode(sfConfig::get('app_email_plugin_to_notification'),";");

    $subject = $this->getSubject();
    $body = $this->getBody(true);

    $link = "mailto:$email?cc=$cc&subject=$subject&body=$body";
    return $link;

  }


  public function getSubject(){
    if(!$this->subject){
      $this->subject = "[".sfConfig::get('app_organisme_nom')."] Résultat de dégustation du ".ucfirst(format_date($this->degustation->date, "P", "fr_FR"));
    }
    return $this->subject;
  }

  public function getBody($forMailLink = false){
    $body = $this->getBodyFromPartial('degustation/notificationEmail', array('degustation' => $this->degustation , 'etablissement' => $this->etablissement, 'mailManager' => $this));
    if($forMailLink){
      return rawurlencode(htmlspecialchars_decode($body));
    }
    return $body;

  }

  public function hasConformes()
  {
    return count($this->etablissementLotsConformes);
  }

  public function hasNonConformes()
  {
    return count($this->etablissementLotsNonConformes);
  }

  public function hasConformesNonConformes()
  {
    return $this->hasConformes() && $this->hasNonConformes();
  }

  public function getEtablissement(){
    return $this->etablissement;
  }
}
