<?php echo use_helper("Date"); ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href="<?php echo url_for('pieces_historique', $etablissement) ?>">Documents</a></li>
</ol>

<div class="page-header">
    <h2>
    	Historique des documents
        <form class="form-inline pull-right col-xs-3">
		  <div class="form-group">
		    <select class="form-control select2 select2SubmitOnChange select2autocomplete input-sm text-right" id="year" name="annee">
		    	<option value="0">Toutes années</option>
		    	<?php foreach ($years as $y): ?>
		    	<option value="<?php echo $y ?>"<?php if($y == $year): ?> selected="selected"<?php endif; ?>><?php echo $y ?></option>
		    	<?php endforeach; ?>
		    </select>
		  </div>
		</form>
    </h2>
</div>


<div class="row">
<div class="list-group col-xs-12">
<?php if(count($history) > 0): ?>
	<ul class="nav nav-pills" style="margin: 0 0 20px 0;">
		<li<?php if (!$category):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'annee' => $year))?>">Tous&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo count($history) - $decreases ?></a></li>
		<?php foreach ($categories as $categorie => $nbDoc): ?>
        <li<?php if ($category && $category == $categorie):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'annee' => $year, 'categorie' => $categorie))?>"><?php echo ($categorie == 'FICHIER')? 'Document' : ucfirst(strtolower($categorie)); ?>&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo $nbDoc ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php
		foreach ($history as $document):
			if ($category && preg_match('/^([a-zA-Z]*)\-./', $document->id, $m)) {
				if ($m[1] != $category) { continue; }
			}
	?>
	<div class="list-group-item col-xs-12">
		<span class="col-xs-2">
			<?php echo (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $document->key[PieceAllView::KEYS_DATE_DEPOT]))? format_date($document->key[PieceAllView::KEYS_DATE_DEPOT], "dd/MM/yyyy", "fr_FR") : null; ?>
		</span>
		<span class="col-xs-8">
			<?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?>
		</span>
		<span class="col-xs-2">
			<a class="pull-right" href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><span class="glyphicon glyphicon-file"></span></a>
			<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			<a class="pull-right" href="<?php echo $urlVisu ?>" style="margin: 0 10px;"><span class=" glyphicon glyphicon-eye-open"></span></a>
			<?php endif; ?>
		</span>
	</div>
	<?php endforeach; ?>
<?php else: ?>
	<p class="text-center"><em>Aucun document disponible<?php if ($year): ?> pour l'année <strong><?php echo $year ?></strong><?php endif; ?></em></p>
<?php endif; ?>
</div>
</div>
