<?php

class DegustationPrelevementLotsForm extends acCouchdbObjectForm {

    private $lots = [];
    private $lotsPrelevables = null;
    protected $date_degustation = null;
    protected $dates_degust_drevs = array();
    protected $object = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $id = $object->_id;
        $this->object = $object;
        strtok($id, '-');
        $this->date_degustation = DateTime::createFromFormat('YmdHi', strtok('-'))->format('Ymd');

        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure() {
        foreach ($this->object->lots as $lot) {
            $this->lots[$lot->unique_id] = $lot;
        }

        foreach (DegustationClient::getInstance()->getLotsPrelevables() as $key => $item) {
            $this->lots[$key] = $item;
        }

        $formLots = new BaseForm();

        foreach ($this->lots as $key => $lot) {
            $formLots->embedForm($key, new DegustationPrelevementLotForm(null, ['lot' => $lot]));
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('prelevement[%s]');

    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $lots = [];
        foreach ($values['lots'] as $id => $val) {
            if (isset($val['preleve']) && !empty($val['preleve'])) {
                $lots[$id] = $this->getLot($id);
            }
        }

        $this->getObject()->setLots($lots);
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();

        foreach ($this->getObject()->lots as $lot) {
          $defaults['lots'][$lot->getUniqueId()] = array('preleve' => 1);
        }

        if(!count($this->getObject()->lots)){
            $nbLots = 0;
            foreach ($this->lotsPrelevables as $key => $item) {
                if (array_key_exists($item->id_document, $this->dates_degust_drevs) === false) {
                    $obj = acCouchdbManager::getClient()->find($item->id_document);
                    $this->dates_degust_drevs[$item->id_document] = date('Ymd');
                    if($obj->exist("date_degustation_voulue") && DateTime::createFromFormat('Y-m-d', $obj->date_degustation_voulue)){
                      $this->dates_degust_drevs[$item->id_document] = DateTime::createFromFormat('Y-m-d', $obj->date_degustation_voulue)->format('Ymd');
                    }
                }

                $preleve = ($this->dates_degust_drevs[$item->id_document] > $this->getDateDegustation()) ? 0 : 1;

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
