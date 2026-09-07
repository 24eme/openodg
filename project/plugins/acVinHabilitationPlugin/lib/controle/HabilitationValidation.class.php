<?php

class HabilitationValidation extends DocumentValidation
{
    private $configuration;
    private $habilitation;

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
        if (!CommunesConfiguration::getInstance()->hasCommunes()) {
            return;
        }

        foreach ($this->document->getActivitesHabilitesByProduits() as $activitesHabilites) {
            if (in_array(HabilitationClient::ACTIVITE_VINIFICATEUR, $activitesHabilites)) {
                $this->controleLocalisation($this->document->declarant);
                break;
            }
        }
    }

    public function controleLocalisation($declarant)
    {
        $code_insee_cvi = substr($declarant->cvi, 0, 5);
        $commune = ucfirst(strtolower($declarant->commune));
        $configurationCommunes = CommunesConfiguration::getInstance();
        $code_insee = $configurationCommunes->findCodeCommune($commune);

        if ($code_insee && $configurationCommunes->getCommuneByCode($code_insee)) {
            return true;
        }
        if ($configurationCommunes->getCommuneByCode($code_insee_cvi)) {
            return true;
        }
        $this->addPoint(self::TYPE_ERROR, 'commune_hors_de_l_aire', "La commune [". $code_insee .' - '.$code_insee_cvi.'] '. $commune ." n'est pas dans la liste des communes reconnues");
        return false;
    }

}
