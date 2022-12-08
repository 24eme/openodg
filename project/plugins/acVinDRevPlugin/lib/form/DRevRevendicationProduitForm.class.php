<?php

class DRevRevendicationProduitForm extends acCouchdbObjectForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->getDocable()->remove();
    }

    public function configure() {
        $this->setWidgets(array(
            'volume_revendique_issu_recolte' => new bsWidgetFormInputFloat(),
        ));

        $this->setValidators(array(
            'volume_revendique_issu_recolte' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->embedForm('recolte', new DRevProduitRecolteForm($this->getObject()->recolte, array_merge($this->getOptions(), array("fields" => array('volume_total', 'recolte_nette', 'volume_sur_place', 'vci_constitue', 'vsi')))));

        $this->getWidget('volume_revendique_issu_recolte')->setAttribute('class', $this->getWidget('volume_revendique_issu_recolte')->getAttribute('class').' input_sum_value');

        if($this->getObject()->getConfig()->hasMutageAlcoolique()) {
            $this->setWidget('volume_revendique_issu_mutage', new bsWidgetFormInputFloat());
            $this->setValidator('volume_revendique_issu_mutage', new sfValidatorNumber(array('required' => false)));
            $this->getWidget('volume_revendique_issu_mutage')->setAttribute('class', $this->getWidget('volume_revendique_issu_mutage')->getAttribute('class').' input_sum_value');
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
      if ($this->getOption('disabled_dr')) {
          foreach($this->getEmbeddedForm('recolte')->getWidgetSchema()->getFields() as $key => $item) {
              if(!$item->getAttribute('disabled')) {
                  continue;
              }
              unset($values['recolte'][$key]);
          }
      }
      if (isset($values['recolte']) && isset($values['recolte']['vci_constitue']) && $values['recolte']['vci_constitue']) {
        $this->getObject()->vci->constitue = $values['recolte']['vci_constitue'];
      }

      parent::doUpdateObject($values);
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      if (is_null($defaults['volume_revendique_issu_recolte']) && ($this->getObject()->canCalculTheoriticalVolumeRevendiqueIssuRecolte())) {
        $defaults['volume_revendique_issu_recolte'] = $this->getObject()->getTheoriticalVolumeRevendiqueIssuRecole();
        if ($defaults['volume_revendique_issu_recolte'] < 0) {
          unset($defaults['volume_revendique_issu_recolte']);
        }
      }
      $this->setDefaults($defaults);
    }


  }
