<?php

class TravauxMarcDistillationForm extends acCouchdbObjectForm
{

    public function configure() {
        $this->setWidget('date_distillation', new sfWidgetFormInput());
        $this->setValidator('date_distillation', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));
        $this->getValidator('date_distillation')->setMessage('bad_format', "Le format de la date n'est pas correct");

        $this->setWidget('distillation_prestataire', new sfWidgetFormInputCheckbox(array(), array('class' => 'bsswitch', 'data-on-text' => 'Oui', 'data-off-text' => 'Non', 'data-on-color' => 'primary')));
        $this->setValidator('distillation_prestataire', new sfValidatorBoolean(array('required' => false)));

        $this->setWidget('alambic_connu', new sfWidgetFormInputCheckbox(array(), array('class' => 'bsswitch', 'data-on-text' => 'Oui', 'data-off-text' => 'Non', 'data-on-color' => 'primary')));
        $this->setValidator('alambic_connu', new sfValidatorBoolean(array('required' => false)));

        $formAdresse = new BaseCouchdbObjectForm($this->getObject()->adresse_distillation);

        $formAdresse->setWidget('adresse', new sfWidgetFormInput());
        $formAdresse->setValidator('adresse', new sfValidatorString(array('required' => false)));
        $formAdresse->setWidget('code_postal', new sfWidgetFormInput());
        $formAdresse->setValidator('code_postal', new sfValidatorString(array('required' => false)));
        $formAdresse->setWidget('commune', new sfWidgetFormInput());
        $formAdresse->setValidator('commune', new sfValidatorString(array('required' => false)));

        $this->embedForm('adresse_distillation', $formAdresse);

        $this->widgetSchema->setNameFormat("travauxmarc_distillation[%s]");
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('date_distillation', $this->getObject()->getDateDistillationFr());
    }


}
