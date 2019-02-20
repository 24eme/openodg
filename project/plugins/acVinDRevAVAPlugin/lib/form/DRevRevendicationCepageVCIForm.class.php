<?php

class DRevRevendicationCepageVCIForm extends acCouchdbObjectForm {

    protected $stock_editable = false;

    public function __construct(acCouchdbJson $object, $stock_editable = false, $options = array(), $CSRFSecret = null) {
		$this->stock_editable = $stock_editable;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
          'stock_precedent' => new sfWidgetFormInputFloat(),
          'destruction' => new sfWidgetFormInputFloat(),
          'complement' => new sfWidgetFormInputFloat(),
          'substitution' => new sfWidgetFormInputFloat(),
          'rafraichi' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'stock_precedent' => 'Stock VCI '.((int) $this->getObject()->getDocument()->campagne-1),
            'destruction' => 'Volumes detruits avant le 31/12 (en hl))',
            'complement' => 'Volumes revendiqués en complément de la récolte (en hl)',
            'substitution' => 'Volumes substitués (en hl)',
            'rafraichi' => 'Volumes rafraichis (en hl)'
        ));
        $this->setValidators(array(
            'stock_precedent' => new sfValidatorNumber(array('required' => false)),
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'destruction' => new sfValidatorNumber(array('required' => false)),
            'complement' => new sfValidatorNumber(array('required' => false)),
            'substitution' => new sfValidatorNumber(array('required' => false)),
            'rafraichi' => new sfValidatorNumber(array('required' => false))
        ));

        if (!$this->stock_editable) {
        	unset($this['stock_precedent']);
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->updateStock();
    }

}
