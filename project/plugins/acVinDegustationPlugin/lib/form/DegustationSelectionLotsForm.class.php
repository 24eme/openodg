<?php

class DegustationSelectionLotsForm extends acCouchdbObjectForm {

    private $lots = [];
    private $leurres = [];
    protected $date_degustation = null;
    protected $dates_degust_drevs = array();
    protected $object = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $id = $object->_id;
        $this->object = $object;
        $this->date_degustation = $object->getDateFormat('Ymd');

        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure() {
        $this->lots = $this->object->getLotsFromProvenance();
        uasort($this->lots, array("DegustationClient", "sortLotByDate"));

        foreach ($this->object->getLots() as $lot) {
            if ($lot->isLeurre()) {
                $this->leurres[] = $lot;
            }
        }

        foreach (DegustationClient::getInstance()->getLotsPrelevables() as $key => $item) {
            if (array_key_exists($item->unique_id, $this->lots)) {
                continue;
            }

            $this->lots[$item->unique_id] = $item;
        }

        $formLots = new BaseForm();

        ksort($this->lots);

        foreach ($this->lots as $key => $lot) {
            $formLots->embedForm($key, new DegustationPrelevementLotForm(null, ['lot' => $lot]));

            if (array_key_exists($lot->id_document, $this->dates_degust_drevs) === false) {
                $doc = acCouchdbManager::getClient()->find($lot->id_document);
                $this->dates_degust_drevs[$lot->id_document] = date('Ymd');
                if($doc->exist("date_degustation_voulue") && DateTime::createFromFormat('Y-m-d', $doc->date_degustation_voulue)){
                    $this->dates_degust_drevs[$lot->id_document] = DateTime::createFromFormat('Y-m-d', $doc->date_degustation_voulue)->format('Ymd');
                }
            }
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('prelevement[%s]');

    }

    protected function doUpdateObject($values) {
        $this->getObject()->fillDocToSaveFromLots();
        parent::doUpdateObject($values);

        $lots = [];
        foreach ($values['lots'] as $id => $val) {
            if (isset($val['preleve']) && !empty($val['preleve'])) {
                $lots[$id] = $this->getLot($id);
            }
        }

        $lots = array_merge($lots, $this->leurres);

        $this->getObject()->setLots($lots);
        if (!$this->getObject()->max_lots < count($lots)) {
            $this->getObject()->max_lots = count($lots);
        }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();

        $nbLots = 0;
        $lots_preleves = [];
        foreach ($this->getObject()->lots as $lot) {
          if ($lot->isLeurre()) {
              continue;
          }
          $defaults['lots'][$lot->getUniqueId()] = array('preleve' => 1);
          $lots_preleves[] = $lot->getUniqueId();
          $nbLots++;
        }

        if (!count($lots_preleves)){

            foreach ($this->lots as $key => $lot) {
                if (in_array($lot->unique_id, $lots_preleves)) {
                    continue;
                }

                $preleve = ($this->dates_degust_drevs[$lot->id_document] > $this->getDateDegustation()) ? 0 : 1;

                if(!is_null($this->getObject()->max_lots) && ($this->getObject()->max_lots <= $nbLots)){
                    $preleve = 0;
                }
                $nbLots+=$preleve;
                $defaults['lots'][$key] = array('preleve' => $preleve);
            }
        }

        $this->setDefaults($defaults);
    }

    public function getDateDegustation()
    {
        return $this->date_degustation;
    }

    public function getDateDegustParDrev()
    {
        return $this->dates_degust_drevs;
    }

    public function getLot($key)
    {
        return $this->lots[$key];
    }
}
