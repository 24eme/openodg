<?php

class PriseDeMousseForm extends acCouchdbObjectForm
{
    public static $types = array(PriseDeMousseClient::CHANGEMENT_TYPE_PRISEDEMOUSSE => "Prise de Mousse");

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $produits = $this->getProduits();
        $cepages = $this->getCepages();

        $this->setWidget('prisedemousse_type', new bsWidgetFormChoice(array('choices' => $this->getTypes(), 'expanded' => true)));
        $this->setValidator('prisedemousse_type', new sfValidatorChoice(array('choices' => array_keys($this->getTypes()), 'required' => true)));

        $this->setWidget('prisedemousse_volume', new bsWidgetFormInputFloat());
        $this->setValidator('prisedemousse_volume', new sfValidatorNumber(array('required' => false)));

        $this->setWidget('prisedemousse_produit_hash', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('prisedemousse_produit_hash', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($produits))));

        for($i = 0; $i < DRevLotForm::NBCEPAGES; $i++) {
            $this->setWidget('cepage_'.$i, new bsWidgetFormChoice(array('choices' => $cepages)));
            $this->setValidator('cepage_'.$i, new sfValidatorChoice(array('required' => false, 'choices' => array_keys($cepages))));
            $this->setWidget('repartition_'.$i, new bsWidgetFormInputFloat([], ['class' => 'form-control text-right input-float input-hl']));
            $this->setValidator('repartition_'.$i, new sfValidatorNumber(array('required' => false)));
        }

        if(ChgtDenomConfiguration::getInstance()->hasSpecificiteLot()){
          $this->setWidget('prisedemousse_specificite', new bsWidgetFormChoice(array('choices' => $this->getSpecificites())));
          $this->setValidator('prisedemousse_specificite', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getSpecificites()))));
        }

        $this->validatorSchema->setPostValidator(new ChgtDenomValidator($this->getObject()));
        $this->widgetSchema->setNameFormat('chgt_denom[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->changement_type = $values['prisedemousse_type'];
        $this->getObject()->changement_produit_hash = null;

        if ($values['prisedemousse_produit_hash'] && !$this->getObject()->isDeclassement()) {
            $this->getObject()->changement_produit_hash = $values['prisedemousse_produit_hash'];
        }
        $this->getObject()->remove('prisedemousse_cepages');
        $this->getObject()->add('prisedemousse_cepages');

        if($values['prisedemousse_type'] !== PriseDeMousseClient::CHANGEMENT_TYPE_DECLASSEMENT){
          for($i = 0; $i < DRevLotForm::NBCEPAGES; $i++) {
              if(!$values['cepage_'.$i] || !$values['repartition_'.$i]) {
                  continue;
              }
              $this->getObject()->addCepage($values['cepage_'.$i], $values['repartition_'.$i]);
          }
        }

        if ($this->getObject()->isFromProduction()) {
            $this->getObject()->origine_volume = $this->getObject()->changement_volume;
        }
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      $defaults['prisedemousse_type'] = $this->getObject()->changement_type;
      $defaults['prisedemousse_volume'] = ($this->getObject()->exist('prisedemousse_volume'))? $this->getObject()->changement_volume : $this->getObject()->getLotOrigine()->volume;
      if (ChgtDenomConfiguration::getInstance()->hasSpecificiteLot()) {
        $defaults['prisedemousse_specificite'] = $this->getObject()->changement_specificite;
      }
      $i=0;
      foreach($this->getObject()->changement_cepages as $cepage => $repartition) {
          $defaults['cepage_'.$i] = $cepage;
          $defaults['repartition_'.$i] = $repartition;
          $i++;
      }
      $this->setDefaults($defaults);
    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if (!$produit->isActif()) {
                continue;
            }
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }
        return array_merge(array('' => ''), $produits);
    }

    public function getCepages()
    {
        return array_merge(array('' => ''), $this->getObject()->getDocument()->getConfiguration()->getCepagesAutorises());
    }

    public function getSpecificites()
    {
        return array_merge(array(Lot::SPECIFICITE_UNDEFINED => "", "" => "Aucune"),  ChgtDenomConfiguration::getInstance()->getSpecificites());
    }

    public function getTypes()
    {
        return $this->getObject()->isFromProduction() ? [PriseDeMousseClient::CHANGEMENT_TYPE_DECLASSEMENT  => "Déclassement"] : self::$types;
    }
}
