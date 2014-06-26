<?php
/**
 * BaseDRevLot
 * 
 * Base model for DRevLot

 * @property string $nb_vtsgn
 * @property string $nb_hors_vtsgn
 * @property string $libelle
 * @property string $hash_produit
 * @property integer $no_vtsgn

 * @method string getNbVtsgn()
 * @method string setNbVtsgn()
 * @method string getNbHorsVtsgn()
 * @method string setNbHorsVtsgn()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getHashProduit()
 * @method string setHashProduit()
 * @method integer getNoVtsgn()
 * @method integer setNoVtsgn()
 
 */

abstract class BaseDRevLot extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevLot';
    }
                
}