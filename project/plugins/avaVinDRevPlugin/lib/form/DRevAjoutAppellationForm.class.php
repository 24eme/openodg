<?php
class DRevAjoutAppellationForm extends acCouchdbObjectForm
{
    protected $produits;
    protected $noeud;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = array();
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure()
    {
        $produits = $this->getProduits();
        $this->setWidgets(array(
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits))
        ));
        $this->widgetSchema->setLabels(array(
            'hashref' => 'Produit: '
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucune appellation saisie."))
        ));
        $this->widgetSchema->setNameFormat('drev_ajout_appellation[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $produits = $this->getObject()->declaration->certification->genre->getConfigChidrenNode();
            foreach ($produits as $produit) {
                if ($this->getObject()->exist($produit->getHash())) {
                    continue;
                }

                $this->produits[$produit->getHash()] = $produit->libelle;
            }
        }

        return array_merge(array('' => ''), $this->produits);
    }

    public function hasProduits()
    {
        return (count($this->getProduits()) > 1);
    }

    protected function doUpdateObject($values)
    {
        if (!isset($values['hashref']) || empty($values['hashref'])) {

            return;
        }

        $this->noeud = $this->getObject()->getDocument()->addAppellation($values['hashref']);
        $this->noeud->getParent()->reorderByConf();
    }

    public function getNoeud() {

        return $this->noeud;
    }
}
