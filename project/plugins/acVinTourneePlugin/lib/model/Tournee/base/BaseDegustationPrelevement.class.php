<?php
/**
 * BaseDegustationPrelevement
 * 
 * Base model for DegustationPrelevement

 * @property string $hash_produit
 * @property string $libelle
 * @property string $anonymat_prelevement
 * @property string $anonymat_degustation
 * @property string $anonymat_prelevement_complet
 * @property string $cuve
 * @property string $preleve
 * @property integer $commission
 * @property string $appreciations
 * @property acCouchdbJson $notes

 * @method string getHashProduit()
 * @method string setHashProduit()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method string getAnonymatPrelevement()
 * @method string setAnonymatPrelevement()
 * @method string getAnonymatDegustation()
 * @method string setAnonymatDegustation()
 * @method string getAnonymatPrelevementComplet()
 * @method string setAnonymatPrelevementComplet()
 * @method string getCuve()
 * @method string setCuve()
 * @method string getPreleve()
 * @method string setPreleve()
 * @method integer getCommission()
 * @method integer setCommission()
 * @method string getAppreciations()
 * @method string setAppreciations()
 * @method acCouchdbJson getNotes()
 * @method acCouchdbJson setNotes()
 
 */

abstract class BaseDegustationPrelevement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Tournee';
       $this->_tree_class_name = 'DegustationPrelevement';
    }
                
}