<?php

class DRevSuperficieProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
    }

    protected function updateDefaultsFromObject() {
        if ($this->getObject()->getDocument()->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) {
          parent::updateDefaultsFromObject();
        }
        $this->setDefault('has_stock_vci',  $this->getObject()->hasVci());
    }

    public function configure() {
        $this->setWidgets(array(
            'superficie_revendique' => new bsWidgetFormInputFloat(),
            'has_stock_vci' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'has_stock_vci' => new sfValidatorBoolean(array('required' => false)),
        ));
        $this->embedForm('recolte', new DRevProduitRecolteForm($this->getObject()->recolte, array_merge($this->getOptions(), array('fields' => array('superficie_total')))));

        if($this->getObject()->hasVci(true)) {
            $this->getWidget('has_stock_vci')->setAttribute('readonly', 'readonly');
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if($values['has_stock_vci'] && !$this->getObject()->hasVci()) {
            $this->getObject()->vci->stock_precedent = 0;
        }
        if(!$values['has_stock_vci']) {
        	$this->getObject()->vci->stock_precedent = null;
        }
    }

}
