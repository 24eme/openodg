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
            $defaults[$key] = $this->secteur;
        }

        parent::__construct($degustation, $defaults, $options, $CSRFSecret);
    }

    private function getSecteurs() {
        $secteurs = [DegustationClient::DEGUSTATION_SANS_SECTEUR => ''];
        foreach ($this->lots_par_logements as $secteur => $lots) {
            if ($secteur == DegustationClient::DEGUSTATION_SANS_SECTEUR) {
                continue;
            }
            $secteurs[$secteur] = $secteur;
        }
        return $secteurs;
    }

    public function configure()
    {
        $logements = [];

        foreach ($this->lots_par_logements[$this->secteur] as $logementKey => $lots) {
            $logements[$logementKey] = $logementKey;
        }

        ksort($logements);

        foreach($logements as $logementKey) {
            $this->setWidget($logementKey, new bsWidgetFormChoice(['choices' => $this->getSecteurs()], ['class' => 'form-control select2SubmitOnChange']));
            $this->setValidator($logementKey, new sfValidatorChoice(['choices' => array_keys($this->getSecteurs())]));
        }

        $this->widgetSchema->setNameFormat('degustation_tournees[%s]');
    }

    public function save()
    {
        foreach($this->getValues() as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            foreach($this->lots_par_logements[$this->secteur][$key] as $lot) {
                $lot->secteur = ($value != DegustationClient::DEGUSTATION_SANS_SECTEUR) ? $value : null;
            }
        }

        $this->doc->save(false);
    }

}
