<?php
/**
 * BaseControle
 *
 * Base model for Controle
 *
 * @property string $_id
 * @property string $_rev
 * @property string $type
 * @property string $date
 * @property string $date_tournee
 * @property string $type_tournee
 * @property string $identifiant
 * @property string $campagne
 * @property ControleDeclarant $declarant
 * @property string $secteur
 * @property acCouchdbJson $liaisons_operateurs
 * @property string $liaisons
 * @property acCouchdbJson $audit
 * @property acCouchdbJson $parcelles
 * @property acCouchdbJson $manquements
 * @property acCouchdbJson $mouvements_statuts

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method string getType()
 * @method string setType()
 * @method string getDate()
 * @method string setDate()
 * @method string getDateTournee()
 * @method string setDateTournee()
 * @method string getTypeTournee()
 * @method string setTypeTournee()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getCampagne()
 * @method string setCampagne()
 * @method ControleDeclarant getDeclarant()
 * @method ControleDeclarant setDeclarant()
 * @method string getSecteur()
 * @method string setSecteur()
 * @method acCouchdbJson getLiaisonsOperateurs()
 * @method acCouchdbJson setLiaisonsOperateurs()
 * @method string getLiaisons()
 * @method string setLiaisons()
 * @method acCouchdbJson getAudit()
 * @method acCouchdbJson setAudit()
 * @method acCouchdbJson getParcelles()
 * @method acCouchdbJson setParcelles()
 * @method acCouchdbJson getManquements()
 * @method acCouchdbJson setManquements()
 * @method acCouchdbJson getMouvementsStatuts()
 * @method acCouchdbJson setMouvementsStatuts()

 */

abstract class BaseControle extends acCouchdbDocument implements InterfaceDeclarantDocument {

    public function getDocumentDefinitionModel() {
        return 'Controle';
    }

}
