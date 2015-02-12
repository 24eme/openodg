<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAjoutParcelleForm
 *
 * @author mathurin
 */
class ParcellaireAjoutParcelleForm extends acCouchdbObjectForm {

    private $appellation;

    public function __construct(acCouchdbJson $object, $appellation, $options = array(), $CSRFSecret = null) {
        $this->appellation = $appellation;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
            'commune' => new sfWidgetFormInput(),
            'section' => new sfWidgetFormInput(),
            'numero_parcelle' => new sfWidgetFormInput(),
        ));
        $this->widgetSchema->setLabels(array(
            'commune' => 'Commune',
            'section' => 'Section',
            'numero_parcelle' => 'Numéro parcelle',
        ));

        $this->setValidators(array(
            'commune' => new sfValidatorString(array('required' => true), array('required' => "Aucune commune saisie.")),
            'section' => new sfValidatorString(array('required' => true), array('required' => "Aucune section saisie.")),
            'numero_parcelle' => new sfValidatorString(array('required' => true), array('required' => "Aucun numéro de parcelle saisi.")),
        ));



        $this->widgetSchema->setNameFormat('parcellaire_ajout_parcelle[%s]');
    }

    protected function doUpdateObject($values) {
        if ((!isset($values['commune']) || empty($values['commune'])) || 
                (!isset($values['section']) || empty($values['section'])) || 
                (!isset($values['numero_parcelle']) || empty($values['numero_parcelle']))
        ) {
            return;
        }

        $commune = $values['commune'];
        $section = $values['section'];
        $numero_parcelle = $values['numero_parcelle'];
        $this->getObject()->addParcelleForAppellation($this->appellation,$commune,$section,$numero_parcelle);
    }

}
