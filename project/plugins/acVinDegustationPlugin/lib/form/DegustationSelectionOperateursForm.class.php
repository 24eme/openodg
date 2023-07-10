<?php

class DegustationSelectionOperateursForm extends acCouchdbObjectForm {

    private $operateurs = [];

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure()
    {
        $this->setWidget('identifiant', new WidgetEtablissement(['interpro_id' => 'INTERPRO-declaration'], [
            'class' => 'form-control select2SubmitOnChange select2autocompleteAjax input-md'
        ]));
        $this->widgetSchema->setLabel('identifiant', 'Sélectionner un établissement&nbsp;:');
        $this->setValidator('identifiant', new ValidatorEtablissement(array('required' => true)));
        $this->validatorSchema['identifiant']->setMessage('required', 'Le choix d\'un etablissement est obligatoire');

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
    }
}
