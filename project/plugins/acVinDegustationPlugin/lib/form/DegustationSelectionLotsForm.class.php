<?php

class DegustationSelectionLotsForm extends acCouchdbObjectForm {

    private $lots = [];
    private $leurres = [];
    private $lotsOperateurs = [];
    private $filter_empty = false;
    private $auto_select_lots = true;
    protected $date_degustation = null;
    protected $dates_degust_drevs = array();
    protected $object = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $id = $object->_id;
        $this->object = $object;
        $this->date_degustation = $object->getDateFormat('Ymd');
        $this->filter_empty = isset($options['filter_empty']) && $options['filter_empty'];

        if (isset($options['auto_select_lots'])) {
            $this->auto_select_lots = $options['auto_select_lots'];
        }

        parent::__construct($object, $options = array(), $CSRFSecret = null);
    }

    public function configure() {
        $this->lots = $this->object->getLotsFromProvenance($this->filter_empty);
        uasort($this->lots, array("DegustationClient", "sortLotByDate"));

        foreach ($this->object->getLots() as $lot) {
            if ($lot->isLeurre()) {
                $this->leurres[] = $lot;
            }
        }

        if ($this->filter_empty) {
            $this->lotsOperateurs = array_filter($this->object->getLots()->toArray(), function ($v) {
                return $v->id_document_provenance === null;
            });
        }
        if($this->getObject()->getType() == DegustationClient::TYPE_MODEL) {
            $lotsDispo = DegustationClient::getInstance()->getLotsEnAttente($this->getObject()->getRegion(), $this->getDateDegustation());
        } elseif($this->getObject()->getType() == TourneeClient::TYPE_MODEL) {
            $lotsDispo = TourneeClient::getInstance()->getLotsEnAttente($this->getObject()->getRegion());
        }
        foreach ($lotsDispo as $item) {
            if (array_key_exists($item->unique_id, $this->lots)) {
                continue;
            }

            $this->lots[$item->unique_id] = $item;
        }

        $formLots = new BaseForm();

        ksort($this->lots);

        foreach ($this->lots as $key => $lot) {
            $formLots->embedForm($key, new DegustationPrelevementLotForm(null, ['lot' => $lot]));

            if ($lot->date_commission) {
                $this->dates_degust_drevs[$lot->unique_id] = $lot->date_commission;
            }

            if (!isset($this->dates_degust_drevs[$lot->unique_id]) || ! $this->dates_degust_drevs[$lot->unique_id]) {
                $lot_origine = LotsClient::getInstance()->findByUniqueId($lot->declarant_identifiant, $lot->unique_id);
                if ($lot_origine && $lot_origine->exist('date_degustation_voulue') && $lot_origine->date_degustation_voulue) {
                    $this->dates_degust_drevs[$lot->unique_id] = $lot_origine->date_degustation_voulue;
                } elseif ($lot_origine && ($doc = $lot_origine->getDocument()) && $doc->exist('date_degustation_voulue') && $doc->date_degustation_voulue) {
                    $this->dates_degust_drevs[$lot->unique_id] = $doc->date_degustation_voulue;
                } else {
                    $this->dates_degust_drevs[$lot->unique_id] = date('Y-m-d');
                }
            }
        }

        if ($this->filter_empty) {
            foreach ($this->lotsOperateurs as $key => $lot) {
                $key = $lot->getUniqueId() ?: $key;
                $formLots->embedForm($key, new DegustationPrelevementLotForm(null, ['lot' => $lot]));
                $this->dates_degust_drevs[$lot->unique_id] = date('Y-m-d');
            }
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('prelevement[%s]');

    }

    protected function doUpdateObject($values) {
        $this->getObject()->fillDocToSaveFromLots();

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
          $uniqueID = $lot->getUniqueId() ?: str_replace('/lots/', '', $lot->getHash());
          $defaults['lots'][$uniqueID] = array('preleve' => 1);
          $lots_preleves[] = $uniqueID;
          $nbLots++;
        }

        if (!count($lots_preleves) && $this->auto_select_lots){

            foreach ($this->lots as $key => $lot) {
                if (in_array($lot->unique_id, $lots_preleves)) {
                    continue;
                }

                $preleve = ($this->dates_degust_drevs[$lot->unique_id] > $this->getDateDegustation()) ? 0 : 1;

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
        if ($this->filter_empty) {
            if (array_key_exists($key, $this->lotsOperateurs)) {
                return $this->lotsOperateurs[$key];
            }

            foreach ($this->lotsOperateurs as $k => $lot) {
                if ($lot->unique_id === $key) {
                    return $lot;
                }
            }
        }

        return $this->lots[$key];
    }
}
