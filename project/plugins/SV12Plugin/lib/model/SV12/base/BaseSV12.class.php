<?php
/**
 * BaseSV12
 * 
 * Base model for SV12
 *
 * @property string $_id
 * @property string $_rev
 * @property acCouchdbJson $_attachments
 * @property string $type
 * @property string $fichier_id
 * @property string $identifiant
 * @property string $date_depot
 * @property string $libelle
 * @property integer $visibilite
 * @property integer $papier
 * @property acCouchdbJson $pieces
 * @property string $campagne

 * @method string getId()
 * @method string setId()
 * @method string getRev()
 * @method string setRev()
 * @method acCouchdbJson getAttachments()
 * @method acCouchdbJson setAttachments()
 * @method string getType()
 * @method string setType()
 * @method string getFichierId()
 * @method string setFichierId()
 * @method string getIdentifiant()
 * @method string setIdentifiant()
 * @method string getDateDepot()
 * @method string setDateDepot()
 * @method string getLibelle()
 * @method string setLibelle()
 * @method integer getVisibilite()
 * @method integer setVisibilite()
 * @method integer getPapier()
 * @method integer setPapier()
 * @method acCouchdbJson getPieces()
 * @method acCouchdbJson setPieces()
 * @method string getCampagne()
 * @method string setCampagne()

 */

abstract class BaseSV12 extends DouaneProduction {

    public function getDocumentDefinitionModel() {
        return 'SV12';
    }

}