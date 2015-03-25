<?php
// /**
//  * Model for DegustationOperateur
//  *
//  */

// class DegustationOperateur extends BaseDegustationOperateur {

//     public function getLotsPrelevement() {
//         $lots = array();

//         foreach($this->lots as $lot) {
//             if(!$lot->prelevement) {
//                 continue;
//             }

//             $lots[$lot->getKey()] = $lot;
//         }

//         return $lots;
//     }

//     public function resetLotsPrelevement() {
//         foreach($this->lots as $lot) {
//             $lot->prelevement = 0;
//         }
//     }

//     public function consoliderInfos() {
//         $compte = CompteClient::getInstance()->findByIdentifiant("E" . $this->getKey());
//         $this->email = $compte->email;
//         $this->telephone_bureau = $compte->telephone_bureau;
//         $this->telephone_prive = $compte->telephone_prive;
//         $this->telephone_mobile = $compte->telephone_mobile;
//         $chai = $compte->findChai($this->adresse, $this->commune, $this->code_postal);

//         if($chai) {
//             $this->lat = $chai->lat;
//             $this->lon = $chai->lon;
//         }

//         if(!$this->lat || !$this->lon) {
//             $coordonnees = $compte->calculCoordonnees($this->adresse, $this->commune, $this->code_postal);
//             if($coordonnees) {
//                 $this->lat = $coordonnees["lat"];
//                 $this->lon = $coordonnees["lon"];
//             }
//         }
//     }

//     public function isPrelever() {

//         foreach($this->prelevements as $prelevement) {
//             if($prelevement->cuve) {

//                 return true;
//             }
//         }

//         return false;
//     }
    
//     public function isDeguste() {
//           foreach($this->prelevements as $prelevement) {
//             if(!is_null($prelevement->anonymat_degustation)) {
//                 return true;
//             }
//         }

//         return false;
//     }
// }