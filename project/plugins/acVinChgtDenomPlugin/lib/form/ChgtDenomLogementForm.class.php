<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    const CHGT_ORIGINE = 'origine';
    const CHGT_CHANGE = 'change';

    private $logement;

    public function __construct(acCouchdbJson $object, $options = [], $CSRFSecret = null)
    {
        $this->logement = (isset($options['logement'])) ? $options['logement'] : self::CHGT_ORIGINE;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
      $this->setWidget('changement_numero_logement_operateur', new bsWidgetFormInput());
      $this->setValidator('changement_numero_logement_operateur', new sfValidatorString());

      $this->setWidget('logement', new sfWidgetFormInputHidden(['default' => $this->logement]));
      $this->setValidator('logement', new sfValidatorString());

      $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }

    public function updateDefaultsFromObject()
    {
        parent::updateDefaultsFromObject();

        if ($this->logement == self::CHGT_ORIGINE) {
            $default['changement_numero_logement_operateur'] = $this->getObject()->changement_numero_logement_operateur_origine;
        } else {
            $default['changement_numero_logement_operateur'] = $this->getObject()->changement_numero_logement_operateur_change;
        }
    }

    public function doUpdateObject($values)
    {
        parent::doUpdateObject($values);

        if ($values['logement'] == self::CHGT_ORIGINE) {
            $this->getObject()->changement_numero_logement_operateur_origine = $values['changement_numero_logement_operateur'];
        } else {
            $this->getObject()->changement_numero_logement_operateur_change = $values['changement_numero_logement_operateur'];
        }
    }
}
