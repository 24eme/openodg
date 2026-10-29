<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAppellationEditForm
 *
 * @author mathurin
 */
class ParcellaireAffectationAppellationEditForm extends acCouchdbObjectForm {

    private $appellationKey;
    private $parcelles;

    public function __construct(\acCouchdbJson $object, $appellationKey, $parcelles,  $options = array(), $CSRFSecret = null) {
        $this->appellationKey = $appellationKey;
        $this->parcelles = $parcelles;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->embedForm('produits', new ParcellaireAppellationProduitsForm($this->parcelles, $this->appellationKey));
        $this->validatorSchema->setPostValidator(new ParcellaireAffectationAppellationProduitsValidator());
        $this->widgetSchema->setNameFormat('parcellaire_parcelles[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }
   }

}
