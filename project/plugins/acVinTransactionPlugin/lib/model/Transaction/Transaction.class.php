<?php

/**
 * Model for Transaction
 *
 */
class Transaction extends BaseTransaction
{
    public function constructId() {
        if (!$this->date) {
            $this->date = date("Y-m-d");
        }
        $idDate = str_replace('-', '', $this->date);
        $id = 'TRANSACTION-' . $this->identifiant . '-' . $idDate;
        if($this->version) {
            $id .= "-".$this->version;
        }
        $this->set('_id', $id);
    }

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
      $date = null;
      if ($this->getValidation()) {
        $dt = new DateTime($this->getValidation());
        $date = $dt->format('Y-m-d');
      }
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $date,
    		'libelle' => 'Déclaration de vrac export '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('transaction_visualisation', array('id' => $id));
    }

    public function findMaster() {
        return TransactionClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, $this->campagne);
    }

    public function findDocumentByVersion($version) {
        $tabId = explode('-', $this->_id);
        if (count($tabId) < 3) {
          throw new sfException("Doc id incoherent");
        }
        $id = $tabId[0].'-'.$tabId[1].'-'.$tabId[2];
        if($version) {
            $id .= "-".$version;
        }
        return acCouchdbManager::getClient()->find($id);
    }
}
