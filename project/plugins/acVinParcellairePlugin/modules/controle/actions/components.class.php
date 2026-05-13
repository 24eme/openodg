<?php use_helper("Date"); ?>
<?php

class controleComponents extends sfComponents {

    public function executePreviewMailPopup(sfWebRequest $request)
    {
        $this->controle = ControleClient::getInstance()->find($request->getParameter('id_controle'));

        $this->subject = sprintf("%s - Suite contrôle interne ODG %s",Organisme::getInstance()->getNom(), $this->controle->getDateFormat('Y'));
        $this->email = EtablissementClient::getInstance()->find($this->controle->identifiant)->getEmail();
        $this->cc = Organisme::getInstance()->getEmail();
    }
}
