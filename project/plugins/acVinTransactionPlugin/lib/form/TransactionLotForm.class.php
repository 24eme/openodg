<?php
class TransactionLotForm extends LotForm
{

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }


    public function configure() {

      	parent::configure();

        unset($this->widgetSchema['destination_type']);
        unset($this->validators['destination_type']);

        $this->setWidget('affectable',new sfWidgetFormInputCheckbox());
        $this->setValidator('affectable', new sfValidatorBoolean(['required' => false]));

        $this->setWidget('pays', new bsWidgetFormChoice(array('choices' => $this->getCountryList())));
        $this->setValidator('pays', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getCountryList()))));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if ($destinationdefaut = TransactionConfiguration::getInstance()->getDestinationDefaut()) {
            $this->setDefault('pays', $destinationdefaut);
        }
    }

    public function doUpdateObject($values) {

        parent::doUpdateObject($values);

        $this->getObject()->set("affectable",true);
        $this->getObject()->destination_type = ($this->getObject()->pays == 'France')? DRevClient::LOT_DESTINATION_VRAC:  DRevClient::LOT_DESTINATION_TRANSACTION;

    }

    public function getCountryList() {
        $destinationChoicesWidget = new bsWidgetFormI18nChoiceCountry(array('culture' => 'fr', 'add_empty' => true));
        $choices = $destinationChoicesWidget->getChoices();
        foreach($destinationChoicesWidget->getChoices() as $choice) {
          $destinationChoices[$choice] = $choice;
        }
        return $destinationChoices;
    }

    public function getSpecificites()
    {
        return array_merge(array(Lot::SPECIFICITE_UNDEFINED => "", "" => "Aucune"),  DRevConfiguration::getInstance()->getSpecificites());
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

    public function getMillesimes() {
        $m = array('NM', 'nm');
        for($i = 0 ; $i < 10 ; $i++) {
            $m[] = date('Y') - $i;
        }
        return $m;
    }

    public function hasSaisieCepages() {
        return LotsClient::getInstance()->saisieMentionCepageActive();
    }

}
