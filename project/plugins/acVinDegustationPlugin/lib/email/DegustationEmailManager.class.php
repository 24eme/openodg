<?php

class DegustationEmailManager extends Email
{
    private static $_instance = null;

    public static function getInstance($context = null) {
        if (is_null(self::$_instance)) {
            self::$_instance = new DegustationEmailManager($context);
        }
        return self::$_instance;
    }

    protected function getBodyFromPartial($partial, $vars = null) {

        return $this->getAction()->getPartial($partial, $vars);
    }

    public function getMailerLink(Degustation $degustation, Etablissement $etablissement) {

      $organisme = sfConfig::get('app_organisme_nom');
      $cc = sfConfig::get('app_email_plugin_from_adresse');
      $to = array($etablissement->email);

      $urlBack = sfContext::getInstance()->getRouting()->generate('degustation_notifications_etape',$degustation, true);
      $debug = false;

      $this->etablissementsLotsConforme = $degustation->getEtablissementLotsConformesOrNot();
      $this->etablissementsLotsNonConforme = $degustation->getEtablissementLotsConformesOrNot(false);

      $uri = sfContext::getInstance()->getRouting()->generate('degustation_conformite_pdf',array('id' => $degustation->_id, 'identifiant' => $etablissement->identifiant),true);

      $email = $etablissement->email;
      $subject = "[".$organisme."] Résultat de dégustation du ".ucfirst(format_date($degustation->date, "P", "fr_FR"));
      $body = "Bonjour%0D%0A%0D%0AVeuillez cliquer sur le lien suivant pour avoir le détail de vos lots dégustés: $uri";
      //$body = self::BODY ."%0D%0A%0D%0A".$urlBase.$uri;
      $link = '<a href="mailto:'.$email."?cc=$cc&subject=$subject&body=$body".'" id="link-mail-auto" data-retour='.$urlBack.' ';
      $link .= ($debug)? '>Ouverture Mailer</a>' : '/>';

      return $link;
        $body = $this->getBodyFromPartial('facturation/email', array('factures' => $facturesToSend));

    }
}
