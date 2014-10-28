<?php
/**
 * BaseDRevCepage
 * 
 * Base model for DRevCepage

 * @property string $libelle
 * @property float $superficie_revendique
 * @property float $superficie_revendique_vt
 * @property float $superficie_revendique_sgn
 * @property float $volume_revendique
 * @property float $volume_revendique_vt
 * @property float $volume_revendique_sgn
 * @property float $volume_revendique_total
 * @property acCouchdbJson $detail

 * @method string getLibelle()
 * @method string setLibelle()
 * @method float getSuperficieRevendique()
 * @method float setSuperficieRevendique()
 * @method float getSuperficieRevendiqueVt()
 * @method float setSuperficieRevendiqueVt()
 * @method float getSuperficieRevendiqueSgn()
 * @method float setSuperficieRevendiqueSgn()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method float getVolumeRevendiqueVt()
 * @method float setVolumeRevendiqueVt()
 * @method float getVolumeRevendiqueSgn()
 * @method float setVolumeRevendiqueSgn()
 * @method float getVolumeRevendiqueTotal()
 * @method float setVolumeRevendiqueTotal()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 
 */

abstract class BaseDRevCepage extends _DRevDeclarationNoeud {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCepage';
    }
                
}