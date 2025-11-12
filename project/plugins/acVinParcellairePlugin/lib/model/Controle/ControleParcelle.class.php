<?php
class ControleParcelle extends BaseControleParcelle
{
    public function  getData() {
        $data = parent::getData();
        $data->geojson = $this->getGeoJson();
        return $data;
    }
}
