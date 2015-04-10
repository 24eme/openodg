<?php
/**
 * BaseDegustationPrelevement
 * 
 * Base model for DegustationPrelevement

 * @property string $hash_produit
 * @property string $libelle
 * @property integer $anonymat_prelevement
 * @property string $anonymat_prelevement_complet
 * @property integer $anonymat_degustation
 * @property string $cuve
 * @property string $preleve
 * @property integer $commission
 * @property string $appreciations
 * @property string $type_courrier
 * @property string $visite_date
 * @property string $visite_heure
 * @property string $courrier_envoye
 * @property acCouchdbJson $notes

 * @method string getHashProduit()
 * @method string setHashProduit()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method integer getAnonymatPrelevement()
 * @method integer setAnonymatPrelevement()
 * @method string getAnonymatPrelevementComplet()
 * @method string setAnonymatPrelevementComplet()
 * @method integer getAnonymatDegustation()
 * @method integer setAnonymatDegustation()
 * @method string getCuve()
 * @method string setCuve()
 * @method string getPreleve()
 * @method string setPreleve()
 * @method integer getCommission()
 * @method integer setCommission()
 * @method string getAppreciations()
 * @method string setAppreciations()
 * @method string getTypeCourrier()
 * @method string setTypeCourrier()
 * @method string getVisiteDate()
 * @method string setVisiteDate()
 * @method string getVisiteHeure()
 * @method string setVisiteHeure()
 * @method string getCourrierEnvoye()
 * @method string setCourrierEnvoye()
 * @method acCouchdbJson getNotes()
 * @method acCouchdbJson setNotes()
 
 */

abstract class BaseDegustationPrelevement extends acCouchdbDocumentTree {
                
    public function configureTree() {
       $this->_root_class_name = 'Degustation';
       $this->_tree_class_name = 'DegustationPrelevement';
    }
                
}