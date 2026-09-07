<?php

class DRCommentaireValidationForm extends acCouchdbForm
{
    public function configure() {
        if(sfContext::getInstance()->getUser()->isAdminODG()) {
            if ($this->getDocument()->exist('commentaire')) {
                $this->setWidget('commentaire', new sfWidgetFormTextarea(array('default' => $this->getDocument()->commentaire)));
                $this->validatorSchema['commentaire'] = new sfValidatorPass();
            }
        }

        $this->widgetSchema->setNameFormat('updateCommentaire[%s]');
    }
}
