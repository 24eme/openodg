<?php
/**
 * BaseDRevLotCepage
 * 
 * Base model for DRevLotCepage

 * @property integer $nb_vtsgn
 * @property integer $nb_hors_vtsgn
 * @property string $libelle
 * @property string $hash

 * @method integer getNbVtsgn()
 * @method integer setNbVtsgn()
 * @method integer getNbHorsVtsgn()
 * @method integer setNbHorsVtsgn()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getHash()
 * @method string setHash()
 
 */

abstract class BaseDRevLotCepage extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevLotCepage';
    }
                
}