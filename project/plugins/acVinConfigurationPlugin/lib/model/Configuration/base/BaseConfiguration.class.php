<?php
/**
 * BaseConfiguration
 * 
 * Base model for Configuration
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $campagne
 * @property string $virtual
 * @property string $dr_non_editable
 * @property acCouchdbJson $intitule
 * @property acCouchdbJson $motif_non_recolte
 * @property ConfigurationDeclaration $declaration

 * @method string get_id()
 * @method string set_id()
 * @method string get_rev()
 * @method string set_rev()
 * @method string getType()
 * @method string setType()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method string getVirtual()
 * @method string setVirtual()
 * @method string getDrNonEditable()
 * @method string setDrNonEditable()
 * @method acCouchdbJson getIntitule()
 * @method acCouchdbJson setIntitule()
 * @method acCouchdbJson getMotifNonRecolte()
 * @method acCouchdbJson setMotifNonRecolte()
 * @method ConfigurationDeclaration getDeclaration()
 * @method ConfigurationDeclaration setDeclaration()
 
 */
 
abstract class BaseConfiguration extends acCouchdbDocument {

    public function getDocumentDefinitionModel() {
        return 'Configuration';
    }
    
}