<?php echo use_helper("Date"); ?>
<ol class="breadcrumb">

    <li><a href="<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?><?php echo url_for('documents'); ?><?php endif; ?>">Documents</a></li>
    <li><a href="<?php echo url_for('pieces_historique', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
</ol>

<div class="page-header">
    <h2>
    	Historique des documents
        <?php if ($sf_user->isAdmin() || $sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
        <a class="btn btn-sm btn-primary pull-right" href="<?php echo url_for('upload_fichier', $etablissement) ?>"><span class="glyphicon glyphicon-plus"></span> Ajouter un document</a>
        <?php endif; ?>
    </h2>
</div>


<div class="list-group">
    <form class="pull-right">
        <select class="form-control select2 select2SubmitOnChange select2autocomplete input-md text-right pull-right" id="year" name="campagne">
            <option value="0">Toutes campagnes</option>
            <?php foreach ($campagnes as $c): ?>
            <option value="<?php echo $c ?>"<?php if($c == $campagne): ?> selected="selected"<?php endif; ?>><?php echo $c ?></option>
            <?php endforeach; ?>
        </select>
    </form>
<?php if(count($history) > 0): ?>
	<ul class="nav nav-pills" style="margin: 0 0 20px 0;">
		<li<?php if (!$category):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'campagne' => $campagne))?>">Tous&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo count($history) - $decreases ?></a></li>
		<?php foreach ($categories as $categorie => $nbDoc): ?>
        <li<?php if ($category && $category == $categorie):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'categorie' => $categorie))?>"><?php echo ($categorie == 'FICHIER')? 'Document' : str_replace('cremant', ' Crémant', ucfirst(strtolower($categorie))); ?>&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo $nbDoc ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php foreach ($history as $document): ?>
		<?php if ($category && strtolower($document->key[PieceAllView::KEYS_CATEGORIE]) != $category) { continue; } ?>
	<div class="list-group-item col-xs-12">
		<span class="col-sm-2 col-xs-12">
			<?php echo format_date(preg_replace('/^([0-9]{4}-[0-9]{2}-[0-9]{2}).*/', '$1', $document->key[PieceAllView::KEYS_DATE_DEPOT]), "dd/MM/yyyy", "fr_FR"); ?>
		</span>
		<span class="col-sm-8 col-xs-12">
			<?php if ((!$sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) || $sf_user->isAdmin()) &&  Piece::isVisualisationMasterUrl($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
				<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
					<a href="<?php echo $urlVisu ?>" ><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
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
				  		<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
				  		<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
				  		<?php endif; ?>
				  	</ul>
				<?php else: ?>
				<a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
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
			  	<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			  	<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
			  	<?php endif; ?>
		  	</ul>
		<?php else: ?>
		<a class="pull-right" href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><span class="glyphicon glyphicon-file"></span></a>
		<?php endif; ?>
		<?php if ((!$sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) || $sf_user->isAdmin()) && $urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			<a class="pull-right" href="<?php echo $urlVisu ?>" style="margin: 0 10px;" data-toggle-second="tooltip" title="Modifier le document"><span class="glyphicon glyphicon-edit"></span></a>
		<?php endif; ?>
		<?php if (Piece::isPieceEditable($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			<a class="pull-right" href="<?php echo url_for('edit_fichier', array('id' => $document->id)) ?>"><span class="glyphicon glyphicon-user"></span></a>
		<?php endif; ?>
		</span>
	</div>
	<?php endforeach; ?>
<?php else: ?>
	<p class="text-center"><em>Aucun document disponible<?php if ($campagne): ?> pour la campagne <strong><?php echo $campagne ?></strong><?php endif; ?></em></p>
<?php endif; ?>
</div>
