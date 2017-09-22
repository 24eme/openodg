<?php
/**
 * BaseHabilitationCepage
 *
 * Base model for HabilitationCepage

 * @property string $libelle
 * @property HabilitationActivites $details

 * @method string getLibelle()
 * @method string setLibelle()
 * @method HabilitationActivites getDetails()
 * @method HabilitationActivites setDetails()

 */

abstract class BaseHabilitationProduit extends acCouchdbDocumentTree {

    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationCepage';
    }

}
