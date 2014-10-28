<?php
/**
 * BaseDRevCepageDetail
 * 
 * Base model for DRevCepageDetail

 * @property string $vtsgn
 * @property float $volume_revendique_vt
 * @property float $volume_revendique_sgn
 * @property float $volume_revendique
 * @property float $volume_revendique_total
 * @property string $lieu

 * @method string getVtsgn()
 * @method string setVtsgn()
 * @method float getVolumeRevendiqueVt()
 * @method float setVolumeRevendiqueVt()
 * @method float getVolumeRevendiqueSgn()
 * @method float setVolumeRevendiqueSgn()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method float getVolumeRevendiqueTotal()
 * @method float setVolumeRevendiqueTotal()
 * @method string getLieu()
 * @method string setLieu()
 
 */

abstract class BaseDRevCepageDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCepageDetail';
    }
                
}