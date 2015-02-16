<?php
/**
 * BaseParcellaireAppellationParcelle
 * 
 * Base model for ParcellaireAppellationParcelle

 * @property float $superficie
 * @property string $commune
 * @property string $code_postal
 * @property string $section
 * @property string $numero_parcelle

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
 
 */

abstract class BaseParcellaireAppellationParcelle extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireAppellationParcelle';
    }
                
}