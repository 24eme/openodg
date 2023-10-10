<?php

class DegustationSelectionOperateursForm extends acCouchdbObjectForm {

    private $operateurs = [];

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure()
    {

        $identifiant_options = ['class' => 'form-control select2autocompleteAjax'];
        $this->setWidget('identifiant', new WidgetEtablissement(['interpro_id' => 'INTERPRO-declaration'], $identifiant_options));

        $this->widgetSchema->setLabel('identifiant', 'Établissement');
        $this->setValidator('identifiant', new ValidatorEtablissement(array('required' => true)));
        $this->validatorSchema['identifiant']->setMessage('required', 'Le choix d\'un etablissement est obligatoire');

        $this->setWidget('initial_type', new bsWidgetFormChoice(['choices' => ['ALEATOIRE' => 'Aléatoire', 'RENFORCE' => 'Aléatoire renforcé']], []));
        $this->widgetSchema->setLabel('initial_type', 'Type de contrôle');
        $this->setValidator('initial_type', new sfValidatorChoice(['choices' => ['ALEATOIRE', 'RENFORCE']]));
        $this->validatorSchema['initial_type']->setMessage('required', 'Le choix d\'un type est obligatoire');

        $this->widgetSchema['details'] = new bsWidgetFormInput();
        $this->widgetSchema['details']->setLabel("Appellation à controler");
        $this->validatorSchema['details'] = new sfValidatorString(array('required' => false));

        $this->widgetSchema->setNameFormat('selection_operateur[%s]');
    }

    protected function doUpdateObject($values)
    {
        $etablissement = EtablissementClient::getInstance()->find($values['identifiant']);
        $lot = $this->getObject()->add('lots')->add();
        $lot->date = date('Y-m-d');
        $lot->id_document = $this->getObject()->_id;
        $lot->declarant_identifiant = $etablissement->getIdentifiant();
        $lot->declarant_nom = $etablissement->getNom();
        $lot->adresse_logement = sprintf('%s, %s %s', $etablissement->getAdresse(), $etablissement->getCodePostal(), $etablissement->getCommune());
        $lot->affectable = false;
        $lot->initial_type = 'Degustation:aleatoire';
        if ($values['initial_type'] === 'RENFORCE') {
            $lot->initial_type .= '_renforce';
        }
        $lot->details = $values['details'];
    }
}
