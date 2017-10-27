<?php

abstract class Piece extends acCouchdbDocumentTree
{
	const MIME_PDF = 'application/pdf';
	const LIMIT_HISTORY = 10;
	
	public function getUrl()
	{
		return $this->getDocument()->generateUrlPiece($this->source);
	}
	
	public static function getUrlVisualisation($id, $isadmin = false)
	{
		if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::getUrlVisualisationPiece($id, $isadmin);

		}
		return null;
	}
	
	public static function isVisualisationMasterUrl($id, $isadmin = false)
	{
		if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $id, $m)) {
			$doc = $m[1];
			return $doc::isVisualisationMasterUrl($isadmin);
		
		}
		return false;
	}
}