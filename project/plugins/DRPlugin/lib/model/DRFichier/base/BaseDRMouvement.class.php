<?php
/**
 * BaseDRMouvement
 *
 * Base model for DRMouvement

 * @property integer $facture
 * @property integer $facturable

 * @method integer getFacture()
 * @method integer setFacture()
 * @method integer getFacturable()
 * @method integer setFacturable()

 */

abstract class BaseDRMouvement extends Mouvement {

    public function configureTree() {
       $this->_root_class_name = 'DR';
       $this->_tree_class_name = 'DRMouvement';
    }

}
