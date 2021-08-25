<?php
/**
 * BaseParcellaireAffectationCertification
 *
 * Base model for ParcellaireCertification

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()

 */

abstract class BaseParcellaireAffectationCertification extends _ParcellaireAffectationDeclarationNoeud {

    public function configureTree() {
       $this->_root_class_name = 'ParcellaireAffectation';
       $this->_tree_class_name = 'ParcellaireAffectationCertification';
    }

}