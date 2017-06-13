<?php echo use_helper("Date"); ?>
<?php if(count($history) > 0): ?>
<h2>Derniers documents</h2>

<div class="row">
<div class="list-group col-xs-12">
<?php $i=0; foreach ($history as $document): $i++; if ($i>$limit) { break; } ?>
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
</div>
</div>
<a href="<?php echo url_for('pieces_historique', $etablissement) ?>" class="pull-right btn btn-warning btn-xs"><span class="glyphicon glyphicon-plus"></span>&nbsp;Plus de document</a>
<?php endif; ?>
