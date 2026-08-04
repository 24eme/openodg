<?php

class HabilitationValidation extends DocumentValidation
{
    private $configuration;

    public function __construct($document, $options = null)
    {
        $this->configuration = $options['configuration'] ?? ConfigurationClient::getInstance()->getCurrent();
        parent::__construct($document, $options);
    }

    public function configure()
    {
        $this->addControle(self::TYPE_ERROR, 'commune_hors_de_l_aire', "Commune inconnue");
    }

    public function controle()
    {
        // print_r(EtablissementClient::getInstance()->findByCvi($this->document->declarant->cvi)->);exit;
        if (array_key_exists('PRODUCTEUR', HabilitationClient::getInstance()->getActivites())) {
            $this->controleLocalisation($this->document->declarant);
        }
    }

    public function controleLocalisation($declarant)
    {
        $code_insee = substr($declarant->cvi, 0, 5);
        $commune = ucfirst(strtolower($declarant->commune));
        $configurationCommunes = CommunesConfiguration::getInstance();

        if ($configurationCommunes->getCommuneByCode($code_insee) != $commune && $configurationCommunes->findCodeCommune($commune) != $code_insee) {
            $this->addPoint(self::TYPE_ERROR, 'commune_hors_de_l_aire', "La commune [". $code_insee .'] '. $commune ." n'est pas dans la liste des communes IGP Atlantique");
            return 0;
        }
    }
}
