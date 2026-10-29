<?php
/**
 * BaseParcellaireAffectationCepageDetail
 *
 * Base model for ParcellaireCepageDetail

 * @property float $superficie
 * @property string $commune
 * @property string $code_postal
 * @property string $section
 * @property string $numero_parcelle
 * @property string $lieu
 * @property string $departement
 * @property integer $active
 * @property integer $vtsgn

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
 * @method string getDepartement()
 * @method string setDepartement()
 * @method integer getActive()
 * @method integer setActive()
 * @method integer getVtsgn()
 * @method integer setVtsgn()

 */

abstract class BaseParcellaireAffectationCepageDetail extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationCepageDetail';
    }

}