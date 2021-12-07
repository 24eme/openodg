<?php

class DRValidation extends DocumentValidation
{
    private $configuration;

    public function __construct($document, $options = null)
    {
        $this->configuration = $options['configuration'] ?? ConfigurationClient::getInstance()->getCurrent();
        parent::__construct($document, $options);
    }

    public function configure()
    {
        $this->addControle(self::TYPE_WARNING, 'rendement_manquant', "Rendement non présent en configuration");
        $this->addControle(self::TYPE_WARNING, 'rendement_ligne_manquante', "Il manque une ligne dans le produit");
        $this->addControle(self::TYPE_WARNING, 'rendement_declaration', "Le rendement n'est pas correct");
    }

    public function controle()
    {
        foreach ($this->document->getProduits() as $produit) {
            $this->controleRendement($produit);
        }
    }

    public function controleRendement($produit)
    {
        $produit_conf = $this->configuration->identifyProductByLibelle($produit['libelle']);

        if (! $produit_conf->hasRendement()) {
            $this->addPoint(self::TYPE_WARNING, 'rendement_manquant', "Il n'y a pas de rendement pour le produit : ".$produit['libelle']);
            return 0;
        }

        if (array_key_exists('04', $produit['lignes']) === false || array_key_exists('05', $produit['lignes']) === false) {
            $this->addPoint(self::TYPE_WARNING, 'rendement_ligne_manquante', "Information manquante pour le produit : ".$produit['libelle']);
            return 0;
        }

        if ($produit['lignes']['05']['val'] / $produit['lignes']['04']['val'] > $produit_conf->getRendement()) {
            $this->addPoint(self::TYPE_WARNING, 'rendement_declaration', "Le rendement du produit <strong>".$produit['libelle']."</strong> est de <strong>".$produit_conf->getRendement()."</strong> hl/ha");
        }
    }
}
