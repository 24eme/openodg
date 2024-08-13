<?php
class DrevRevendicationAjoutProduitForm extends acCouchdbForm
{
    protected $produits;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = array();
        parent::__construct($object, array(), $options, $CSRFSecret);
    }

    public function configure()
    {
        $produits = $this->getProduits();
        $this->setWidgets(array(
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits))
        ));
        $this->widgetSchema->setLabels(array(
            'hashref' => 'Appellation: '
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucune appellation saisi."))
        ));
        if(DrevConfiguration::getInstance()->hasMentionsComplementaire()) {
            $this->widgetSchema['denomination_complementaire'] = new sfWidgetFormInput();
            $this->widgetSchema['denomination_complementaire']->setLabel("");
            $this->validatorSchema['denomination_complementaire'] = new sfValidatorString(array('required' => false));
        }
        if(DrevConfiguration::getInstance()->hasDenominationAuto()) {
            $this->widgetSchema['denomination_auto'] = new sfWidgetFormChoice(array('expanded' => true, 'choices' => $this->getDenominationAuto(), 'renderer_options' => array('formatter' => array($this, 'formatter'))));
            $this->widgetSchema['denomination_auto']->setLabel("");
            $this->validatorSchema['denomination_auto'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getDenominationAuto())));
            $this->setDefault('denomination_auto', DRevClient::DENOMINATION_CONVENTIONNEL);
        }
        $this->widgetSchema->setNameFormat('drev_revendication_ajout_produit[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $produits = $this->getDocument()->getConfigProduits();
            foreach ($produits as $produit) {
                if (!$produit->isActif()) {
                	continue;
                }

                $this->produits[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }
        return array_merge(array('' => ''), $this->produits);
    }

    public function hasProduits()
    {
        return (count($this->getProduits()) > 1);
    }

    public function getDenominationAuto() {

        return DRevClient::getDenominationsAuto();
    }

    public function formatter($widget, $inputs)
    {
        $rows = array();
        foreach ($inputs as $input)
        {
          $rows[] = $widget->renderContentTag('div', $input['input']."&nbsp;&nbsp;".$this->getOption('label_separator').$input['label'], array('class' => ''));
        }

        return !$rows ? '' : implode($widget->getOption('separator'), $rows);
    }

    public function save()
    {
        $values = $this->getValues();
        $denomination_complementaire = (isset($values['denomination_complementaire']) && !empty($values['denomination_complementaire']))? ($values['denomination_complementaire']) : null;

        if(isset($values['denomination_auto']) && $values['denomination_auto'] && $values['denomination_auto'] != DRevClient::DENOMINATION_CONVENTIONNEL) {
            $denomination_complementaire = $this->getDenominationAuto()[$values['denomination_auto']];
        }

        if (isset($values['hashref']) && !empty($values['hashref'])) {
            $this->getDocument()->addProduit($values['hashref'], $denomination_complementaire);
        }

        $this->getDocument()->save();
    }

}
