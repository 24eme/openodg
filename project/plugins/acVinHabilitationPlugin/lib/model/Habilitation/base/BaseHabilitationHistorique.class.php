<?php
/**
 * BaseHabilitationHistorique
 * 
 * Base model for HabilitationHistorique

 * @property string $iddoc
 * @property string $date
 * @property string $auteur
 * @property string $description

 * @method string getIddoc()
 * @method string setIddoc()
 * @method string getDate()
 * @method string setDate()
 * @method string getAuteur()
 * @method string setAuteur()
 * @method string getDescription()
 * @method string setDescription()
 
 */

abstract class BaseHabilitationHistorique extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Habilitation';
       $this->_tree_class_name = 'HabilitationHistorique';
    }
                
}