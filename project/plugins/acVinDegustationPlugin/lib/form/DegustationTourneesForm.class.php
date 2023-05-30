<?php
class DegustationTourneesForm extends acCouchdbObjectForm
{
    private $degustation;
    private $lots_par_logements;

    private $regions = [];

    public function __construct(Degustation $degustation, $options = array(), $CSRFSecret = null)
    {
        $this->degustation = $degustation;
        $this->lots_par_logements = $this->degustation->getLotsByLogements();
        $this->regions = sfConfig::get('app_donnees_viticoles_regions', ['' => '']);
        parent::__construct($this->degustation, $options, $CSRFSecret);
    }

    public function configure()
    {
        foreach ($this->lots_par_logements as $logement_key => $lots) {
            $name = $this->getWidgetNameFromLogt($logement_key);
            $this->setWidget($name , new sfWidgetFormSelect(['choices' => ['' => ''] + $this->regions], ['required' => false]));
            $this->setValidator($name, new sfValidatorString(['required' => false]));
        }

        $this->widgetSchema->setNameFormat('degustation_modification[%s]');
    }

    public function getRegions()
    {
        return $this->regions;
    }

    public function getLotsByLogements()
    {
        return $this->lots_par_logements;
    }

    public function getFromLogt($logement_key) {
        $name = $this->getWidgetNameFromLogt($logement_key);
        return $this->offsetGet($name);
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->lots_par_logements as $logement_key => $lots) {
            $name = $this->getWidgetNameFromLogt($logement_key);
            foreach ($lots as $lot) {
                if ($values[$name]) {
                    $lot->secteur = $values[$name];
                }
            }
        }
    }

    protected function updateDefaultsFromObject()
    {
        $defaults = $this->getDefaults();
        foreach ($this->lots_par_logements as $logement_key => $lots) {
            $name = $this->getWidgetNameFromLogt($logement_key);
            $secteur = $lots[0]->getEtablissement();
            if ($lots[0]->exist('secteur')) {
                $defaults[$name] = $lots[0]->secteur;
            }
            else {
                $defaults[$name] = $secteur->getRegion();
            }
        }

        $this->setDefaults($defaults);
    }


    protected function doSave($con = null)
    {
        $this->updateObject();
        $this->object->getCouchdbDocument()->save(false);
    }

    private function getWidgetNameFromLogt($logement_key)
    {
        return 'logement_'.hash('md5', $logement_key);
    }
}