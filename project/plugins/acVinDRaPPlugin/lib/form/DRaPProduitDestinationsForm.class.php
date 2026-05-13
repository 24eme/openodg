<?php

class DRaPProduitDestinationsForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidgets(array(
            'superficie' => new bsWidgetFormInputFloat(array(
                            'default' => $this->getObject()->superficie)),
            'destination' => new bsWidgetFormInput(),
        ));

        $this->setValidators(array(
            'superficie' => new sfValidatorNumber(array('required' => true)),
            'destination' => new sfValidatorString(array('required' => true)),
            ));
        $this->widgetSchema->setNameFormat('parcellaire_destinations[%s]');
    }

}
