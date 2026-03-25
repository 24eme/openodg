<?php use_helper("Date"); ?>
<?php

class controleComponents extends sfComponents {

    public function executePreviewMailPopup(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));

        $this->subject = sprintf("%s - Résultat du contrôle du %s",Organisme::getInstance(null, 'controle')->getNom(), ucfirst(format_date($this->controle->getDateFormat(), "P", "fr_FR")));
        $this->email = EtablissementClient::getInstance()->find($this->controle->identifiant)->getEmail();
        $this->cc = Organisme::getInstance(null, 'controle')->getEmail();
    }
}
