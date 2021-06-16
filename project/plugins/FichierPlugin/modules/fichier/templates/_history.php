<?php echo use_helper("Date"); ?>
<?php if(count($history) > 0): ?>
<div class="page-header">
<h2>Derniers documents <a href="<?php echo url_for('pieces_historique', $etablissement) ?>" class="pull-right btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span>&nbsp;Voir tous les documents</a></h2>
</div>

<div class="list-group">
<?php $i=0; foreach ($history as $document): $i++; if ($i>$limit) { break; } ?>
<div class="list-group-item col-xs-12">
	<span class="col-sm-2 col-xs-12">
		<?php echo format_date(preg_replace('/^([0-9]{4}-[0-9]{2}-[0-9]{2}).*/', '$1', $document->key[PieceAllView::KEYS_DATE_DEPOT]), "dd/MM/yyyy", "fr_FR"); ?>
	</span>
	<span class="col-sm-8 col-xs-12">
		<?php if (Piece::isVisualisationMasterUrl($document->id, $sf_user->hasDrevAdmin())): ?>
			<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasDrevAdmin())): ?>
				<a href="<?php echo $urlVisu ?>"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
			<?php endif; ?>
		<?php else: ?>
			<?php if($document->value[PieceAllView::VALUES_FICHIERS] && count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
			  	<a href="#" class="dropdown-toggle" type="button" data-toggle="dropdown" data-toggle-second="tooltip" title="Accéder au documents" aria-haspopup="true" aria-expanded="false"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
			  	<ul class="dropdown-menu">
			  		<?php
			  			foreach ($document->value[PieceAllView::VALUES_FICHIERS] as $file):
			    		$infos = explode('.', $file);
			    		$extention = (isset($infos[1]))? $infos[1] : "";
			  		?>
			  		<li><a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>?file=<?php echo $file ?>"><?php echo strtoupper($extention) ?></a></li>
			  		<?php endforeach; ?>
			  		<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, $sf_user->hasDrevAdmin())): ?>
			  		<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
			  		<?php endif; ?>
			  	</ul>
			<?php else: ?>
			<a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>" ><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
			<?php endif; ?>
		<?php endif; ?>
	</span>
	<span class="col-sm-2 col-xs-12">
		<?php if($document->value[PieceAllView::VALUES_FICHIERS] && count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
		  	<a href="#" class="pull-right dropdown-toggle" type="button" data-toggle="dropdown" data-toggle-second="tooltip" title="Accéder au documents" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-duplicate"></span></a>
		  	<ul class="dropdown-menu">
		  		<?php
		  			foreach ($document->value[PieceAllView::VALUES_FICHIERS] as $file):
		    		$infos = explode('.', $file);
		    		$extention = (isset($infos[1]))? $infos[1] : "";
		  		?>
		  		<li><a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>?file=<?php echo $file ?>"><?php echo strtoupper($extention) ?></a></li>
		  		<?php endforeach; ?>
		  		<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, $sf_user->hasDrevAdmin())): ?>
		  		<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
		  		<?php endif; ?>
		  	</ul>
		<?php else: ?>
		<a class="pull-right" href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><span class="glyphicon glyphicon-file"></span></a>
		<?php endif; ?>
		<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasDrevAdmin())): ?>
			<a class="pull-right" href="<?php echo $urlVisu ?>" style="margin: 0 10px;" data-toggle-second="tooltip" title="Modifier le document" ><span class="glyphicon glyphicon-edit"></span></a>
		<?php endif; ?>
		<?php if (Piece::isPieceEditable($document->id, $sf_user->hasDrevAdmin())): ?>
			<a class="pull-right" href="<?php echo url_for('edit_fichier', array('id' => $document->id)) ?>"><span class="glyphicon glyphicon-user"></span></a>
		<?php endif; ?>
	</span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
