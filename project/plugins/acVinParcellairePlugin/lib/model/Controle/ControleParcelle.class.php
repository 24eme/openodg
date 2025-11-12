<?php
class ControleParcelle extends BaseControleParcelle
{
    public function  getData() {
        $data = parent::getData();
        $data->geojson = $this->getGeoJson();
        $data->kml_placemark = $this->getKMLPlacemark();
        return $data;
    }

    public function getKMLPlacemark() {
        $feat_str = $this->getGeoJson();
        $geojson = json_decode($feat_str);
        $feat_obj = GeoPHP::load($geojson, 'geojson');
        if (!isset($geojson->properties) || !$geojson->properties || isset($geojson->properties->error)) {
            return;
        }
        $kml = '<Placemark>';
        $kml .= '<name>'.$geojson->properties->commune. ' - ' .$geojson->properties->section. ' ' . $geojson->properties->numero.'</name>';
        $kml .= '<description><![CDATA[';
        foreach ($geojson->properties->parcellaires as $key => $parcellaire_detail) {
            if ($_SERVER['SERVER_PORT'] == 443) {
                $url = 'https';
            }else{
                $url = 'http';
            }
            $url .= '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'#/'.$this->getDocument()->_id.'/parcelle/'.$this->getKey();
            $kml .= '<p><a href="'.$url.'">Controle la parcelle '.$this->getKey().'</a></p>';
            foreach (["Commune","Lieu dit","Produit","Cepage","Superficie"] as $prop) {
                if ($prop == "Lieu dit" && ! $parcellaire_detail->{$prop}) {
                    continue;
                }
                $kml .= '<p>' . $prop . ' : ' . $parcellaire_detail->{$prop} . '</p>';
            }
            $kml .= "<p>-----------------</p>";
        }
        $kml .= ']]></description>';
        $kml .= '<styleUrl>#parcelle-style</styleUrl>';
        $kml .= $feat_obj->out('kml');
        $kml .= '</Placemark>';
        return $kml;
    }
}
