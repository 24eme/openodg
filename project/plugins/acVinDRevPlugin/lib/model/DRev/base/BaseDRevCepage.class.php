<?php
/**
 * BaseDRevCepage
 * 
 * Base model for DRevCepage

 * @property string $libelle
 * @property float $superficie_total
 * @property float $volume_sur_place
 * @property float $volume_total

 * @method string getLibelle()
 * @method string setLibelle()
 * @method float getSuperficieTotal()
 * @method float setSuperficieTotal()
 * @method float getVolumeSurPlace()
 * @method float setVolumeSurPlace()
 * @method float getVolumeTotal()
 * @method float setVolumeTotal()
 
 */

abstract class BaseDRevCepage extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCepage';
    }
                
}