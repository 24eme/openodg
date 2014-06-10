<?php
/**
 * BaseDRevCouleur
 * 
 * Base model for DRevCouleur

 * @property string $libelle
 * @property integer $actif
 * @property float $total_superficie
 * @property float $volume_revendique

 * @method string getLibelle()
 * @method string setLibelle()
 * @method integer getActif()
 * @method integer setActif()
 * @method float getTotalSuperficie()
 * @method float setTotalSuperficie()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 
 */

abstract class BaseDRevCouleur extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCouleur';
    }
                
}