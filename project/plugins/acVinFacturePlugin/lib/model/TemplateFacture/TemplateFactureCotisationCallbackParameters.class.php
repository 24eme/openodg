<?php

class TemplateFactureCotisationCallbackParameters
{
    private $doc = null;
    private $parameters = [];

    private $allowedTypes = [
        'appellations',
        'millesime',
        'deja',
        'famille',
        'round_methode',
        'origine',
        'precision',
        'campagne',
        'region',
        'secteur',
        'tranche'
    ];

    public function __construct($doc, array $params)
    {
        $this->doc = $doc;
        $this->parameters = $params;

        $this->checkConfig();
    }

    private function checkConfig()
    {
        $diff = array_diff(array_keys($this->parameters), $this->allowedTypes);

        if (count($diff) > 0) {
            throw new sfException(
                sprintf("Types non autorisés dans la configuration : %s", implode(', ', $diff))
            );
        }

        if (isset($this->parameters['round_methode'])) {
            if (in_array($this->parameters['round_methode'], ['ceil', 'floor']) === false) {
                throw new sfException(
                    sprintf(
                        "Méthode d'arrondi non supportée : %s. Méthodes supportées : [%s]",
                        $this->parameters['round_methode'], implode(', ', ['ceil', 'floor'])
                    )
                );
            }
        }
    }

    public function hasRegion()
    {
        return $this->doc->exist('region') === true;
    }

    public function getRegion()
    {
        return $this->doc->region;
    }

    public function getTypes()
    {
        return array_keys($this->parameters);
    }

    public function getParameters($type = null)
    {
        if ($this->hasRegion()) {
            $this->parameters['region'] = $this->getRegion();
        }

        if ($type) {
            return @$this->parameters[$type];
        }

        return $this->parameters;
    }
}
