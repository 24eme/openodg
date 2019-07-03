<?php
class DRevLotForm extends acCouchdbObjectForm
{

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        $this->setDefault('date', $this->getObject()->getDateFr());
    }

    public function configure() {
        $produits = $this->getProduits();

        $this->setWidget('volume', new bsWidgetFormInputFloat());
        $this->setValidator('volume', new sfValidatorNumber(array('required' => false)));

        $this->setWidget('millesime', new bsWidgetFormInput());
        $this->setValidator('millesime', new sfValidatorInteger(array('required' => false)));

        $this->setWidget('numero', new bsWidgetFormInput());
        $this->setValidator('numero', new sfValidatorString(array('required' => false)));

        $this->setWidget('date', new bsWidgetFormInput());
        $this->setValidator('date', new sfValidatorDate(
            array('date_output' => 'Y-m-d',
            'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
            'required' => false)));

        $this->setWidget('produit_hash', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('produit_hash', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($produits))));;

        $this->setWidget('destination', new bsWidgetFormChoice(array('choices' => $this->getDestinations())));
        $this->setValidator('destination', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getDestinations()))));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

    public function getDestinations()
    {
        return array_merge(array("" => ""), DRevClient::$lotDestinations);
    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }
        return array_merge(array('' => ''), $produits);
    }

}
