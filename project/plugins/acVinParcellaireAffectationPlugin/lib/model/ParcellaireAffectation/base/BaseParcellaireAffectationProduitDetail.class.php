<?php
/**
 * BaseParcellaireAffectationProduitDetail
 * 
 * Base model for ParcellaireAffectationProduitDetail

 * @property float $superficie
 * @property string $commune
 * @property string $code_commune
 * @property string $section
 * @property string $numero_parcelle
 * @property string $idu
 * @property string $lieu
 * @property string $cepage
 * @property string $departement
 * @property integer $active
 * @property integer $vtsgn
 * @property string $campagne_plantation
 * @property string $materiel
 * @property string $ressource
 * @property string $irrigation
 * @property string $date_irrigation

 * @method float getSuperficie()
 * @method float setSuperficie()
 * @method float getSuperficieAffectation()
 * @method float setSuperficieAffectation()

 * @method string getCommune()
 * @method string setCommune()
 * @method string getCodeCommune()
 * @method string setCodeCommune()
 * @method string getSection()
 * @method string setSection()
 * @method string getNumeroParcelle()
 * @method string setNumeroParcelle()
 * @method string getIdu()
 * @method string setIdu()
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
 * @method string getCampagnePlantation()
 * @method string setCampagnePlantation()
 * @method string getMateriel()
 * @method string setMateriel()
 * @method string getRessource()
 * @method string setRessource()
 * @method string getIrrigation()
 * @method string setIrrigation()
 * @method string getDateIrrigation()
 * @method string setDateIrrigation()

 */

abstract class BaseParcellaireAffectationProduitDetail extends DeclarationParcellaireParcelle {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationProduitDetail';
    }

}
