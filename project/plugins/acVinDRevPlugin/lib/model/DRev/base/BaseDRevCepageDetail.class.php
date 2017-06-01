<?php
/**
 * BaseDRevCepageDetail
 * 
 * Base model for DRevCepageDetail

 * @property float $volume_revendique_vt
 * @property float $volume_revendique_sgn
 * @property float $volume_revendique
 * @property float $volume_revendique_total
 * @property float $superficie_vinifiee_total
 * @property float $superficie_vinifiee
 * @property float $superficie_vinifiee_vt
 * @property float $superficie_vinifiee_sgn
 * @property float $superficie_revendique
 * @property float $superficie_revendique_vt
 * @property float $superficie_revendique_sgn
 * @property float $superficie_revendique_total
 * @property string $lieu
 * @property string $libelle

 * @method float getVolumeRevendiqueVt()
 * @method float setVolumeRevendiqueVt()
 * @method float getVolumeRevendiqueSgn()
 * @method float setVolumeRevendiqueSgn()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method float getVolumeRevendiqueTotal()
 * @method float setVolumeRevendiqueTotal()
 * @method float getSuperficieVinifieeTotal()
 * @method float setSuperficieVinifieeTotal()
 * @method float getSuperficieVinifiee()
 * @method float setSuperficieVinifiee()
 * @method float getSuperficieVinifieeVt()
 * @method float setSuperficieVinifieeVt()
 * @method float getSuperficieVinifieeSgn()
 * @method float setSuperficieVinifieeSgn()
 * @method float getSuperficieRevendique()
 * @method float setSuperficieRevendique()
 * @method float getSuperficieRevendiqueVt()
 * @method float setSuperficieRevendiqueVt()
 * @method float getSuperficieRevendiqueSgn()
 * @method float setSuperficieRevendiqueSgn()
 * @method float getSuperficieRevendiqueTotal()
 * @method float setSuperficieRevendiqueTotal()
 * @method string getLieu()
 * @method string setLieu()
 * @method string getLibelle()
 * @method string setLibelle()
 
 */

abstract class BaseDRevCepageDetail extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCepageDetail';
    }
                
}