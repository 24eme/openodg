<?php
/**
 * BaseDRevPrelevements
 * 
 * Base model for DRevPrelevements

 * @property string $cuve_alsace
 * @property string $cuve_vtsgn
 * @property string $bouteille_alsace
 * @property string $bouteille_alsace_grdcru
 * @property string $bouteille_vtsgn

 * @method string getCuveAlsace()
 * @method string setCuveAlsace()
 * @method string getCuveVtsgn()
 * @method string setCuveVtsgn()
 * @method string getBouteilleAlsace()
 * @method string setBouteilleAlsace()
 * @method string getBouteilleAlsaceGrdcru()
 * @method string setBouteilleAlsaceGrdcru()
 * @method string getBouteilleVtsgn()
 * @method string setBouteilleVtsgn()
 
 */

abstract class BaseDRevPrelevements extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevPrelevements';
    }
                
}