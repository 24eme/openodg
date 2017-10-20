<?php

class DRevRevendicationProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

    public function configure() {
        $this->setWidgets(array(
            'volume_revendique_issu_recolte' => new bsWidgetFormInputFloat(),
        ));

        $this->setValidators(array(
            'volume_revendique_issu_recolte' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->embedForm('recolte', new DRevProduitRecolteForm($this->getObject()->recolte, array_merge($this->getOptions(), array("fields" => array('volume_total', 'recolte_nette', 'volume_sur_place', 'vci_constitue')))));

        $this->getWidget('volume_revendique_issu_recolte')->setAttribute('class', $this->getWidget('volume_revendique_issu_recolte')->getAttribute('class').' input_sum_value');

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
