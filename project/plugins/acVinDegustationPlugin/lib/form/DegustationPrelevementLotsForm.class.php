<?php

class DegustationPrelevementLotsForm extends acCouchdbObjectForm {

    private $lotsPrelevables = null;
    protected $date_degustation = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $id = $object->_id;
        strtok($id, '-');
        $this->date_degustation = DateTime::createFromFormat('YmdHi', strtok('-'))->format('Ymd');

        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure() {
        $this->lotsPrelevables = $this->getLotsPrelevables();
        $formLots = new BaseForm();
        foreach ($this->lotsPrelevables as $key => $item) {
            $formLots->embedForm($key, new DegustationPrelevementLotForm());
        }
        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('prelevement[%s]');

    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $mvtkeys = array();
        foreach ($values['lots'] as $id => $val) {
            $mvtkeys[$id] = (isset($val['preleve']) && !empty($val['preleve']));
        }
        $this->getObject()->setLotsFromMvtKeys($mvtkeys, Lot::STATUT_ATTENTE_PRELEVEMENT);
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();

        foreach ($this->getObject()->lots as $lot) {
          $key = $lot->getGeneratedMvtKey();
          $defaults['lots'][$key] = array('preleve' => 1);
        }

        if(!count($this->getObject()->lots)){
            $drevs = [];
            foreach ($this->lotsPrelevables as $key => $item) {
                if (array_key_exists($item->id_document, $drevs) === false) {
                    $drev = DRevClient::getInstance()->find($item->id_document);
                    $drevs[$item->id_document] = DateTime::createFromFormat('Y-m-d', $drev->date_degustation_voulue)->format('Ymd');
                }

                $preleve = ($drevs[$item->id_document] > $this->getDateDegustation()) ? 0 : 1;
                $defaults['lots'][$key] = array('preleve' => $preleve);
            }
        }
        $this->setDefaults($defaults);
    }

    public function getLotsPrelevables() {
        return $this->getObject()->getLotsPrelevables();
    }

    public function getDateDegustation()
    {
        return $this->date_degustation;
    }
}
