<?php
/**
 * BaseParcellaireIrrigableProduitDetail
 * 
 * Base model for ParcellaireIrrigableProduitDetail

 * @property float $superficie
 * @property string $commune
 * @property string $code_postal
 * @property string $section
 * @property string $numero_parcelle
 * @property string $lieu
 * @property string $cepage
 * @property string $departement
 * @property integer $active
 * @property integer $vtsgn
 * @property string $code_insee
 * @property string $ecart_rang
 * @property string $ecart_pieds
 * @property string $campagne_plantation
 * @property string $mode_savoirfaire
 * @property string $porte_greffe
 * @property string $irrigable

 * @method float getSuperficie()
 * @method float setSuperficie()
 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodePostal()
 * @method string setCodePostal()
 * @method string getSection()
 * @method string setSection()
 * @method string getNumeroParcelle()
 * @method string setNumeroParcelle()
 * @method string getLieu()
 * @method string setLieu()
 * @method string getCepage()
 * @method string setCepage()
 * @method string getDepartement()
 * @method string setDepartement()
 * @method integer getActive()
 * @method integer setActive()
 * @method integer getVtsgn()
 * @method integer setVtsgn()
 * @method string getCodeInsee()
 * @method string setCodeInsee()
 * @method string getEcartRang()
 * @method string setEcartRang()
 * @method string getEcartPieds()
 * @method string setEcartPieds()
 * @method string getCampagnePlantation()
 * @method string setCampagnePlantation()
 * @method string getModeSavoirfaire()
 * @method string setModeSavoirfaire()
 * @method string getPorteGreffe()
 * @method string setPorteGreffe()
 * @method string getIrrigable()
 * @method string setIrrigable()
 
 */

abstract class BaseParcellaireIrrigableProduitDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'ParcellaireIrrigable';
       $this->_tree_class_name = 'ParcellaireIrrigableProduitDetail';
    }
                
}