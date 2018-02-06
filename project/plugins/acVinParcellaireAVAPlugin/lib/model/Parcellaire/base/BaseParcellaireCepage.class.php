<?php
/**
 * BaseParcellaireCepage
 * 
 * Base model for ParcellaireCepage

 * @property string $libelle
 * @property acCouchdbJson $acheteurs
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method acCouchdbJson getAcheteurs()
 * @method acCouchdbJson setAcheteurs()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseParcellaireCepage extends _ParcellaireDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'Parcellaire';
       $this->_tree_class_name = 'ParcellaireCepage';
    }
                
}