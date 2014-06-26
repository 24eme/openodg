<?php
/**
 * BaseDRevCouleur
 * 
 * Base model for DRevCouleur

 * @property string $libelle
 * @property float $total_superficie
 * @property float $volume_revendique
 * @property acCouchdbJson $dr

 * @method string getLibelle()
 * @method string setLibelle()
 * @method float getTotalSuperficie()
 * @method float setTotalSuperficie()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method acCouchdbJson getDr()
 * @method acCouchdbJson setDr()
 
 */

abstract class BaseDRevCouleur extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCouleur';
    }
                
}