<?php
class DegustationTourneesForm extends acCouchdbForm
{
    private $lots_par_logements;
    private $regions = null;
    private $secteur = null;

    public function __construct(Degustation $degustation, $secteur, $options = array(), $CSRFSecret = null)
    {
        $this->secteur = $secteur;
        $this->lots_par_logements = $degustation->getLotsBySecteur();
        $defaults = [];
        foreach($this->lots_par_logements[$this->secteur] as $key => $lots) {
            $defaults[$key] = true;
        }

        parent::__construct($degustation, $defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        $logements = [];

        foreach ($this->lots_par_logements[$this->secteur] as $logementKey => $lots) {
            $logements[$logementKey] = $logementKey;
        }

        foreach ($this->lots_par_logements['SANS_SECTEUR'] as $logementKey => $lots) {
            $logements[$logementKey] = $logementKey;
        }

        ksort($logements);

        foreach($logements as $logementKey) {
            $this->setWidget($logementKey, new WidgetFormInputCheckbox());
            $this->setValidator($logementKey, new ValidatorBoolean());
        }

        $this->widgetSchema->setNameFormat('degustation_tournees[%s]');
    }

    public function save()
    {
        foreach($this->getValues() as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            $lots = [];
            if(isset($this->lots_par_logements['SANS_SECTEUR'][$key])) {
                $lots = $this->lots_par_logements['SANS_SECTEUR'][$key];
            }

            if(isset($this->lots_par_logements[$this->secteur][$key])) {
                $lots = $this->lots_par_logements[$this->secteur][$key];
            }
            foreach($lots as $lot) {
                $lot->secteur = ($value) ? $this->secteur : null;
            }
        }

        $this->doc->save(false);
    }

}
