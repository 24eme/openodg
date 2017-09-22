<?php
/**
 * BaseDRevCouleur
 *
 * Base model for DRevCouleur

 * @property string $libelle
 * @property float $volume_revendique
 * @property float $volume_revendique_vtsgn
 * @property float $superficie_revendique
 * @property float $superficie_revendique_vtsgn
 * @property float $superficie_vinifiee
 * @property float $superficie_vinifiee_vtsgn
 * @property acCouchdbJson $detail
 * @property acCouchdbJson $detail_vtsgn

 * @method string getLibelle()
 * @method string setLibelle()
 * @method float getVolumeRevendique()
 * @method float setVolumeRevendique()
 * @method float getVolumeRevendiqueVtsgn()
 * @method float setVolumeRevendiqueVtsgn()
 * @method float getSuperficieRevendique()
 * @method float setSuperficieRevendique()
 * @method float getSuperficieRevendiqueVtsgn()
 * @method float setSuperficieRevendiqueVtsgn()
 * @method float getSuperficieVinifiee()
 * @method float setSuperficieVinifiee()
 * @method float getSuperficieVinifieeVtsgn()
 * @method float setSuperficieVinifieeVtsgn()
 * @method acCouchdbJson getDetail()
 * @method acCouchdbJson setDetail()
 * @method acCouchdbJson getDetailVtsgn()
 * @method acCouchdbJson setDetailVtsgn()

 */

abstract class BaseDRevProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'DRev';
       $this->_tree_class_name = 'DRevCouleur';
    }

}
