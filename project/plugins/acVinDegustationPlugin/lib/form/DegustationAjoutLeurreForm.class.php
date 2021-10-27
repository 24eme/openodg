<?php

class DegustationAjoutLeurreForm extends acCouchdbObjectForm
{
    protected $produits;
    protected $table;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = array();
        $this->table = (isset($options['table'])) ? $options['table'] : null;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure()
    {
        $produits = $this->getProduits();

        if (! $this->table) {
            $this->table = null;
        }
        $this->setDefault('table', $this->table);

        $this->setWidgets(array(
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits)),
            'cepages' => new sfWidgetFormTextarea(),
            'millesime' => new sfWidgetFormInput()
        ));

        $this->widgetSchema->setLabels(array(
            'hashref' => 'Appellation: ',
            'cepages' => 'Cépages: ',
            'millesime' => 'Millésime: '
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucune appellation saisie.")),
            'cepages' => new sfValidatorString(array('required' => false)),
            'millesime' => new sfValidatorInteger(['min' => 0, 'required' => true])
        ));

        $this->setDefault('millesime', ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent());

        $this->widgetSchema['table'] = new sfWidgetFormInputHidden();
        $this->validatorSchema['table'] = new sfValidatorInteger(['required' => true, 'min' => 0]);

        $this->widgetSchema->setNameFormat('degustation_ajout_leurre[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $produits = $this->getObject()->getConfigProduits();
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

    protected function doUpdateObject($values)
    {
        $degust = $this->getObject();
        $hash = ($values['hashref']) ?: null;
        $cepages = ($values['cepages']) ?: null;
        $millesime = $values['millesime'];

        if (isset($hash) && !empty($hash) && array_key_exists($hash, $this->getProduits())) {
            $leurre = $degust->addLeurre($hash, $cepages, $values['table']);
            $leurre->millesime = $millesime;
        }
    }

    protected function doSave($con = null) {
        $this->updateObject();
        $this->object->getCouchdbDocument()->save(false);
    }

}
