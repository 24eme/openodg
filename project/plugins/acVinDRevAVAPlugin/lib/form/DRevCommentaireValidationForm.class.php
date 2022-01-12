<?php

class DRevCommentaireValidationForm extends acCouchdbForm
{
    public function configure() {
        if(sfContext::getInstance()->getUser()->isAdmin()) {
            $this->setWidget('commentaire', new sfWidgetFormTextarea(array('default' => $this->getDocument()->commentaire)));
            $this->validatorSchema['commentaire'] = new sfValidatorPass();
        }

        $this->widgetSchema->setNameFormat('updateCommentaire[%s]');
    }
}
