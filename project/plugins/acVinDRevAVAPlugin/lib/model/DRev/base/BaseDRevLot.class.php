<?php
/**
 * BaseDRevLot
 * 
 * Base model for DRevLot

 * @property string $nb_vtsgn
 * @property string $nb_hors_vtsgn
 * @property string $libelle
 * @property string $libelle_produit
 * @property string $hash_produit
 * @property string $vtsgn
 * @property float $volume_revendique
 * @property integer $no_vtsgn

 * @method string getNbVtsgn()
 * @method string setNbVtsgn()
 * @method string getNbHorsVtsgn()
 * @method string setNbHorsVtsgn()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getLibelleProduit()
 * @method string setLibelleProduit()
 * @method string getHashProduit()
 * @method string setHashProduit()
 * @method string getVtsgn()
 * @method string setVtsgn()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method integer getNoVtsgn()
 * @method integer setNoVtsgn()
 
 */

abstract class BaseDRevLot extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevLot';
    }
                
}