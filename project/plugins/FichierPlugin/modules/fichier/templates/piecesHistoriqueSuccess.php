<?php echo use_helper("Date"); ?>
<?php echo use_helper("Lot"); ?>
<ol class="breadcrumb">
    <li><a href="<?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?><?php echo url_for('documents'); ?><?php endif; ?>">Documents</a></li>
    <li><a href="<?php echo url_for('pieces_historique', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
</ol>
<div class="row">
    <div class="col-sm-9 col-xs-12">
    <h2 style="margin-top: 0; margin-bottom: 20px;">Historique des documents</h2>
    <?php //ATTENTION DUPLIQUÉ pour la version desktop plus bas ?>
    <div class="visible-xs col-xs-6">
    <?php if ($sf_user->isAdminODG() || $sf_user->hasHabilitation()): ?>
    <a style="margin-bottom: 20px;" class="btn btn-block btn-sm btn-default" href="<?php echo url_for('upload_fichier', $etablissement) ?>"><span class="glyphicon glyphicon-plus"></span> Ajouter un document</a>
    <?php endif; ?>
    </div>
    <div class="visible-xs col-xs-6">
    <form>
        <select class="form-control select2 select2SubmitOnChange select2autocomplete text-center" id="year" name="campagne">
            <option value="0">Toutes les campagnes</option>
            <?php foreach ($campagnes as $c): ?>
            <option value="<?php echo $c ?>"<?php if($c == $campagne): ?> selected="selected"<?php endif; ?>><?php echo $c ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    </div>

    <?php if(count($history) > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="col-xs-1">Date</th>
                <th class="col-xs-2">Type</th>
                <th class="">Nom</th>
                <th style="width: 90px !important;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $document): ?>
        		<?php if ($category && strtolower($document->key[PieceAllView::KEYS_CATEGORIE]) != $category) { continue; } ?>
                <tr>
                    <td><?php if($document->key[PieceAllView::KEYS_DATE_DEPOT]): ?><?php echo format_date(preg_replace('/^([0-9]{4}-[0-9]{2}-[0-9]{2}).*/', '$1', $document->key[PieceAllView::KEYS_DATE_DEPOT]), "dd/MM/yyyy", "fr_FR"); ?><?php endif; ?></td>
                    <td><?php echo ($document->key[PieceAllView::KEYS_CATEGORIE] == 'FICHIER')? 'Document' : str_replace('cremant', ' Crémant', clarifieTypeDocumentLibelle(ucfirst(strtoupper($document->key[PieceAllView::KEYS_CATEGORIE])))); ?></td>
                    <td>
                        <?php if ((!$sf_user->hasHabilitation() || $sf_user->isAdmin()) &&  Piece::isVisualisationMasterUrl($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
            				<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
            					<a href="<?php echo $urlVisu ?>" ><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
            				<?php endif; ?>
            			<?php else: ?>
            				<?php if($document->value[PieceAllView::VALUES_FICHIERS] && count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
                            <span class="dropdown">
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
                            </span>
            				<?php else: ?>
            				<a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
            				<?php endif; ?>
            			<?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php if (Piece::hasUrlPublic($document->id)): ?>
                        <a style="opacity: 0.3" onclick="navigator.clipboard.writeText(this.href); alert('Le lien a été copié dans le presse papier !'); return false;" title="Lien public pour partage" class="pull-left" href="<?php echo url_for('piece_public_view', array('doc_id' => $document->id, 'source' => $document->key[PieceAllView::KEYS_SOURCE], 'auth' => UrlSecurity::generateAuthKey($document->id.$document->key[PieceAllView::KEYS_SOURCE]))) ?>"><span class="glyphicon glyphicon-link"></span></a>
                        <?php endif; ?>
                        <?php if (Piece::isPieceEditable($document->id, $sf_user->isAdminODG())): ?>
                			<a href="<?php echo url_for('edit_fichier', array('id' => $document->id)) ?>"><span class="glyphicon glyphicon-user"></span></a>
                		<?php endif; ?>
                        <?php if ((!$sf_user->hasHabilitation() || $sf_user->isAdminODG()) && $urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
                            <a href="<?php echo $urlVisu ?>" style="margin: 0 5px;" data-toggle-second="tooltip" title="Modifier le document"><span class="glyphicon glyphicon-edit"></span></a>
                		<?php endif; ?>
                        <?php if($document->value[PieceAllView::VALUES_FICHIERS] && count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
                            <span class="dropdown">
                                <a href="#" class="dropdown-toggle" type="button" data-toggle="dropdown" data-toggle-second="tooltip" title="Accéder au documents" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-duplicate"></span></a>
                                <ul class="dropdown-menu text-left">
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
                            </span>
                        <?php else: ?>
                        <a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><span class="glyphicon glyphicon-file"></span></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="text-center"><em>Aucun document disponible<?php if ($campagne): ?> pour la campagne <strong><?php echo $campagne ?></strong><?php endif; ?></em></p>
    <?php endif; ?>
    </div>
    <div class="col-sm-3 col-xs-12 page-sidebar">
    <?php //ATTENTION DUPLIQUÉ pour la version mobile plus haut ?>
    <div class="hidden-xs">
    <?php if ($sf_user->isAdminODG() || $sf_user->hasHabilitation()): ?>
    <a style="margin-bottom: 20px;" class="btn btn-block btn-sm btn-default" href="<?php echo url_for('upload_fichier', $etablissement) ?>"><span class="glyphicon glyphicon-plus"></span> Ajouter un document</a>
    <?php endif; ?>
    <form>
        <select class="form-control select2 select2SubmitOnChange select2autocomplete text-center" id="year" name="campagne">
            <option value="0">Toutes les campagnes</option>
            <?php foreach ($campagnes as $c): ?>
            <option value="<?php echo $c ?>"<?php if($c == $campagne): ?> selected="selected"<?php endif; ?>><?php echo $c ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    </div>
    <h4 style="margin-top: 20px;">Types de document</h4>
    <div class="list-group">
	<a class="list-group-item <?php if (!$category):?>active<?php endif; ?>" href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'campagne' => $campagne))?>">Tous<span class="badge" style="position: absolute; right: 10px;"><?php echo count($history) - $decreases ?></span></a>
	<?php foreach ($categories as $categorie => $nbDoc): ?>
    <a class="list-group-item <?php if ($category && $category == $categorie):?>active<?php endif; ?>" href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'categorie' => $categorie))?>"><?php echo ($categorie == 'FICHIER')? 'Document' : str_replace('cremant', ' Crémant', clarifieTypeDocumentLibelle(ucfirst(strtoupper($categorie)))); ?><span class="badge" style="position: absolute; right: 10px;"><?php echo $nbDoc ?></span></a>
	<?php endforeach; ?>
    </div>
</div>
</div>
