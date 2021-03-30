<?php
class Conditionnement extends BaseConditionnement
{
    public function constructId() {
        if (!$this->date) {
            $this->date = date("Y-m-d");
        }
        $idDate = str_replace('-', '', $this->date);
        if (strlen($idDate) < 8) {
            throw new sfException(" mauvaise date pour une transaction");
        }
        $id = 'CONDITIONNEMENT-' . $this->identifiant . '-' . $idDate;
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
    		'libelle' => 'Déclaration de conditionnement '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('conditionnement_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('conditionnement_visualisation', array('id' => $id));
    }

    public function getStatutRevendique() {

        return Lot::STATUT_CONDITIONNE;
    }

}
