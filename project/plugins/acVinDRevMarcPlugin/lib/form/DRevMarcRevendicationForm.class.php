<?php

class DRevMarcRevendicationForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {

        $this->widgetSchema->setNameFormat('drevmarc_revendication[%s]');
        $this->setWidget('debut_distillation', new sfWidgetFormInput());
        $this->setValidator('debut_distillation', new sfValidatorRegex(array('pattern' => '/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/', 'required' => true)));
        $this->getWidget('debut_distillation')->setLabel("du");

        $this->setWidget('fin_distillation', new sfWidgetFormInput());
        $this->setValidator('fin_distillation', new sfValidatorRegex(array('pattern' => '/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/', 'required' => true)));
        $this->getWidget('fin_distillation')->setLabel("au");

        $this->setWidget('qte_marc', new sfWidgetFormInputFloat());
        $this->setValidator('qte_marc', new sfValidatorNumber(array('required' => true, 'min' => 50)));
        $this->getWidget('qte_marc')->setLabel("Quantité de marc mise en oeuvre :");

        $this->setWidget('volume_obtenu', new sfWidgetFormInputFloat());
        $this->setValidator('volume_obtenu', new sfValidatorNumber(array('required' => true)));
        $this->getWidget('volume_obtenu')->setLabel("Volume total obtenu :");

        $this->setWidget('titre_alcool_vol', new sfWidgetFormInputFloat());
        $this->setValidator('titre_alcool_vol', new sfValidatorNumber(array('required' => true, 'min' => 45)));
        $this->getWidget('titre_alcool_vol')->setLabel("Titre alcoométrique volumique :");

        $this->validatorSchema['debut_distillation']->setMessage('invalid', 'La date de début de distillation doit être au format jj/mm/aaaa.');
        $this->validatorSchema['debut_distillation']->setMessage('invalid', 'La date de début de distillation doit être au format jj/mm/aaaa.');
        $this->validatorSchema['fin_distillation']->setMessage('invalid', 'La date de début de distillation doit être au format jj/mm/aaaa.');
        $this->validatorSchema['debut_distillation']->setMessage('required', 'La date de début de distillation est obligatoire.');
        $this->validatorSchema['fin_distillation']->setMessage('required', 'La date de début de distillation est obligatoire.');

        $this->validatorSchema['qte_marc']->setMessage('required', 'La quantité de marc est obligatoire.');
        $this->validatorSchema['qte_marc']->setMessage('invalid', 'La quantité de marc doit être un nombre.');
        $this->validatorSchema['qte_marc']->setMessage('min', 'La quantité de marc minimale est de 50kg.');

        $this->validatorSchema['volume_obtenu']->setMessage('required', 'Le volume total obtenu est obligatoire.');
        $this->validatorSchema['volume_obtenu']->setMessage('invalid', 'Le volume total obtenu doit être un nombre.');

        $this->validatorSchema['titre_alcool_vol']->setMessage('required', 'Le titre alcoométrique volumique est obligatoire.');
        $this->validatorSchema['titre_alcool_vol']->setMessage('invalid', 'Le titre alcoométrique volumique doit être un nombre.');
        $this->validatorSchema['titre_alcool_vol']->setMessage('min', 'Le titre alcoométrique volumique ne doit pas être inférieur à 45°.');

        $this->validatorSchema->setPostValidator(new ValidatorDRevMarcRevendication());
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->debut_distillation = Date::getIsoDateFromFrenchDate($values['debut_distillation']);
        $this->getObject()->fin_distillation = Date::getIsoDateFromFrenchDate($values['fin_distillation']);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if ($this->getObject()->debut_distillation) {
            $this->setDefault('debut_distillation', Date::francizeDate($this->getObject()->debut_distillation));
        }
        if ($this->getObject()->fin_distillation) {
            $this->setDefault('fin_distillation', Date::francizeDate($this->getObject()->fin_distillation));
        }
    }

}
