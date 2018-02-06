<?php
/**
 * BaseParcellaireCertification
 * 
 * Base model for ParcellaireCertification

 * @property string $libelle

 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseParcellaireCertification extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireCertification';
    }
                
}