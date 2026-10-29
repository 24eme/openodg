<?php
class DrevRevendicationProduitDenominationAutoForm extends acCouchdbForm
{
    protected $produit = null;

    public function __construct(acCouchdbJson $produit, $options = array(), $CSRFSecret = null)
    {
        $defaults = array();

        if($produit->denomination_complementaire && !isset(array_flip($this->getDenominationAuto())[$produit->denomination_complementaire])) {
            throw new Exception("La dénomination n'est pas modifiable pour ce produit");
        }

        $defaults['denomination_auto'] = DRevClient::DENOMINATION_CONVENTIONNEL;

        if($produit->denomination_complementaire) {
            $defaults['denomination_auto'] =  array_flip($this->getDenominationAuto())[$produit->denomination_complementaire];
        }
        $this->produit = $produit;
        parent::__construct($produit->getDocument(), $defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        $this->widgetSchema['denomination_auto'] = new sfWidgetFormChoice(array('expanded' => true, 'choices' => $this->getDenominationAuto(), 'renderer_options' => array('formatter' => array($this, 'formatter'))));
        $this->widgetSchema['denomination_auto']->setLabel("");
        $this->validatorSchema['denomination_auto'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getDenominationAuto())));

        $this->widgetSchema->setNameFormat('drev_revendication_produit_denomination_auto[%s]');
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
        $denomination_complementaire = $this->getDenominationAuto()[$values['denomination_auto']];
        if($values['denomination_auto'] == DRevClient::DENOMINATION_CONVENTIONNEL) {
            $denomination_complementaire = null;
        }
        $detailKey = DRev::buildDetailKey($denomination_complementaire);
        if($this->produit->getParent()->exist($detailKey)) {

            return;
        }

        $newProduit = $this->produit->getParent()->add($detailKey, $this->produit);
        $newProduit->denomination_complementaire = $denomination_complementaire;
        $newProduit->libelle = null;
        $newProduit->getLibelle();

        $this->produit->getParent()->remove($this->produit->getKey());
        $this->getDocument()->save();
    }

}
