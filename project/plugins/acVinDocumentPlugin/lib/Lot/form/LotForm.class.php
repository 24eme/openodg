<?php
class LotForm extends acCouchdbObjectForm
{
    const NBCEPAGES = 5;
    protected $all_produits = false;
    private $specificites = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->specificites = [];
        if (isset($options['specificites'])){
            $this->specificites = (array) $options['specificites'];
        }elseif (DRevConfiguration::getInstance()->hasSpecificiteLot()) {
            $this->specificites = DRevConfiguration::getInstance()->getSpecificites();
        }
        parent::__construct($object, $options, $CSRFSecret);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if (method_exists($this->getObject(), 'getDestinationDateFr')) {
            $this->setDefault('destination_date', $this->getObject()->getDestinationDateFr());
        }
        $cepages = array();
        $i=0;
        foreach($this->getObject()->cepages as $cepage => $repartition) {
            $this->setDefault('cepage_'.$i, $cepage);
            $this->setDefault('repartition_hl_'.$i, ($repartition != -1) ? $repartition : null);
            $this->setDefault('repartition_pc_'.$i, ($repartition != -1) ? $repartition : null);
            $i++;
        }

        if(!$this->getObject()->millesime) {
            $this->setDefault("millesime", preg_replace('/-.*/', '', $this->getObject()->campagne));
        }
    }

    public function configure() {
        $produits = $this->getProduits();
        $cepages = $this->getCepages();

        $this->setWidget('volume', new bsWidgetFormInputFloat());
        $this->setValidator('volume', new sfValidatorNumber(array('required' => false)));

        $this->setWidget('millesime', new bsWidgetFormInput());
        $this->setValidator('millesime', new sfValidatorChoice(array('required' => false, 'choices' => $this->getMillesimes())));

        $this->setWidget('numero_logement_operateur', new bsWidgetFormInput());
        $this->setValidator('numero_logement_operateur', new sfValidatorString(array('required' => false)));

        $this->setWidget('destination_date', new bsWidgetFormInput());
        $this->setValidator('destination_date', new sfValidatorDate(
            array('date_output' => 'Y-m-d',
            'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
            'required' => false)));

        $this->setWidget('produit_hash', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('produit_hash', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($produits))));

        $this->setWidget('destination_type', new bsWidgetFormChoice(array('choices' => $this->getDestinationsType())));
        $this->setValidator('destination_type', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getDestinationsType()))));

        if($this->specificites && count($this->specificites)) {
          $this->setWidget('specificite', new bsWidgetFormChoice(array('choices' => $this->getSpecificites())));
          $this->setValidator('specificite', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getSpecificites()))));
        }
        for($i = 0; $i < self::NBCEPAGES; $i++) {
            if ($cepages && count($cepages)) {
                $this->setWidget('cepage_'.$i, new bsWidgetFormChoice(array('choices' => $cepages)));
                $this->setValidator('cepage_'.$i, new sfValidatorChoice(array('required' => false, 'choices' => array_keys($cepages))));
                $this->getValidator('cepage_'.$i)->setMessage('invalid', "Cepage non valide. Choix possibles : ".join(', ', $cepages));
            }else{
                $this->setWidget('cepage_'.$i, new bsWidgetFormInput());
                $this->setValidator('cepage_'.$i, new sfValidatorString(array('required' => false)));
            }
            $this->setWidget('repartition_hl_'.$i, new bsWidgetFormInputFloat([], ['class' => 'form-control text-right input-float input-hl']));
            $this->setValidator('repartition_hl_'.$i, new sfValidatorNumber(array('required' => false)));
        }

        $this->setWidget('elevage', new sfWidgetFormInputCheckbox());
        $this->setValidator('elevage', new sfValidatorBoolean(['required' => false]));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $this->getObject()->remove('cepages');
        $this->getObject()->add('cepages');
        for($i = 0; $i < self::NBCEPAGES; $i++) {
            if(! isset($values['cepage_'.$i]) || !$values['cepage_'.$i]) {
                continue;
            }

            $this->getObject()->addCepage($values['cepage_'.$i], isset($values['repartition_hl_'.$i]) ? $values['repartition_hl_'.$i] : -1);
        }
        if (!empty($values['elevage'])) {
          $this->getObject()->statut = Lot::STATUT_ELEVAGE;
        }
        $this->getObject()->set("affectable",true);
    }

    public function getDestinationsType()
    {
        return array_merge(array("" => ""), DRevClient::getLotDestinationsTypes());
    }

    public function getSpecificites()
    {
        return array_merge(array(Lot::SPECIFICITE_UNDEFINED => "", "" => "Aucune"),  $this->specificites);
    }

    public function getProduits()
    {
        $produits = array();
        $has_all_produits = $this->all_produits || (($this->getObject()->getDocument()->type == ConditionnementClient::TYPE_MODEL) && ConditionnementConfiguration::getInstance()->hasAllProduits());
        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if(!$has_all_produits && !$produit->isRevendicationParLots()) {
                continue;
            }
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
        for($i = 0 ; $i < 15 ; $i++) {
            $m[] = date('Y') - $i;
        }
        return $m;
    }
}
