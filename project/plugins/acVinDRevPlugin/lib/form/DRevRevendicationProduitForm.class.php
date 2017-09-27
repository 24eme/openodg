<?php

class DRevRevendicationProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('has_stock_vci',  $this->getObject()->hasVci());
    }

    public function configure() {
        /*$this->setWidgets(array(
            'superficie_revendique' => new sfWidgetFormInputFloat(),
            'volume_revendique' => new sfWidgetFormInputFloat()
        ));
        $this->widgetSchema->setLabels(array(
            'superficie_revendique' => 'Superficie totale (ares):',
            'volume_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));*/

        $this->setWidgets(array(
            'superficie_revendique' => new bsWidgetFormInputFloat(),
            'volume_revendique_sans_vci' => new bsWidgetFormInputFloat(),
            'vci_complement_dr' => new bsWidgetFormInputFloat(),
            'has_stock_vci' => new sfWidgetFormInputCheckbox(),
        ));
        /*$this->widgetSchema->setLabels(array(
x            'superficie_revendique' => 'Superficie revendiqué (ares):',
            'volume_revendique_sans_vci' => 'Volume revendiqué sans VCI (hl):',
            'vci_complement_dr' => 'Volume revendiqué avec VCI (hl):',
            'vci_stock_initial' => 'Stock VCI avant récolte (hl):',
        ));*/
        $this->setValidators(array(
            'superficie_revendique' => new sfValidatorNumber(array('required' => false)),
            'volume_revendique_sans_vci' => new sfValidatorNumber(array('required' => false)),
            'vci_complement_dr' => new sfValidatorNumber(array('required' => false)),
            'has_stock_vci' => new sfValidatorBoolean(array('required' => false)),
        ));
        $this->embedForm('detail', new DRevRevendicationProduitDRForm($this->getObject()->detail, $this->getOptions()));

        $this->getWidget('volume_revendique_sans_vci')->setAttribute('class', $this->getWidget('volume_revendique_sans_vci')->getAttribute('class').' input_sum_value');
        $this->getWidget('vci_complement_dr')->setAttribute('class', $this->getWidget('vci_complement_dr')->getAttribute('class').' input_sum_value');

        if($this->getObject()->hasVci(true)) {
            $this->getWidget('has_stock_vci')->setAttribute('readonly', 'readonly');
        }

        /*if ($this->getObject()->detail->superficie_total) {
            unset($this->widgetSchema['superficie_revendique']);
            unset($this->validatorSchema['superficie_revendique']);
        }*/

        /*if($this->getObject()->canHaveVtsgn()) {
            $this->setWidget('superficie_revendique_vtsgn', new sfWidgetFormInputFloat());
            $this->setWidget('volume_revendique_vtsgn', new sfWidgetFormInputFloat());

            $this->widgetSchema->setLabel('superficie_revendique_vtsgn', 'Superficie totale (ares):');
            $this->widgetSchema->setLabel('volume_revendique_vtsgn', 'Volume revendiqué (hl)');

            $this->setValidator('superficie_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));
            $this->setValidator('volume_revendique_vtsgn', new sfValidatorNumber(array('required' => false)));

            if ($this->getObject()->detail_vtsgn->superficie_total) {
                unset($this->widgetSchema['superficie_revendique_vtsgn']);
                unset($this->validatorSchema['superficie_revendique_vtsgn']);
            }
        }*/

        /*if ($this->getObject()->canHaveSuperficieVinifiee()) {
        	$this->setWidget('superficie_vinifiee', new sfWidgetFormInputFloat());
        	$this->getWidget('superficie_vinifiee')->setLabel("Superficie vinifiée (ares):");
        	$this->setValidator('superficie_vinifiee', new sfValidatorNumber(array('required' => false)));

        	if($this->getObject()->canHaveVtsgn()) {
        		$this->setWidget('superficie_vinifiee_vtsgn', new sfWidgetFormInputFloat());
        		$this->getWidget('superficie_vinifiee_vtsgn')->setLabel("Superficie vinifiée (ares):");
        		$this->setValidator('superficie_vinifiee_vtsgn', new sfValidatorNumber(array('required' => false)));
        	}
        }*/

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if($values['has_stock_vci'] && !$this->getObject()->hasVci()) {
            $this->getObject()->vci_stock_initial = 0;
        }
        if(!$values['has_stock_vci']) {
        	$this->getObject()->vci_stock_initial = null;
        	$this->getObject()->vci_stock_final = null;
        }
    }

}
