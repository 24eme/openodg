<?php
class avaComponents extends sfComponents 
{
    
    public function executeHistory(sfWebRequest $request) 
    {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->limit = Piece::LIMIT_HISTORY;
        $this->history = PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant);
    }
    
}
