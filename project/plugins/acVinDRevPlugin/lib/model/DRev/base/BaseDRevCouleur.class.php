<?php
/**
 * BaseDRevCouleur
 * 
 * Base model for DRevCouleur

 * @property string $libelle
 * @property float $volume_revendique
 * @property float $superficie_revendique
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method float getSuperficieRevendique()
 * @method float setSuperficieRevendique()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseDRevCouleur extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCouleur';
    }
                
}