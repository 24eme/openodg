<?php use_helper('Date'); ?>

<?php if (!$sf_user->hasDrevAdmin()): ?>
    <?php return; ?>
<?php endif; ?>
<?php if (class_exists("DRClient") && ($etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR  || $etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) && in_array('drev', sfConfig::get('sf_enabled_modules'))): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">DR  <?php echo $campagne; ?></h3>
        </div>
            <div class="panel-body">
                <p>Espace de saisie de la Déclaration de Récolte pour le déclarant.</p>
                <div style="<?php if($dr): ?>margin-top: 76px;<?php else: ?>margin-top: 47px;<?php endif; ?>">
                    <?php if(!$dr): ?>
                    <a class="btn btn-default btn-block" href="<?php echo url_for('scrape_fichier', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'type' => DRClient::TYPE_MODEL)) ?>"><span class="glyphicon glyphicon-cloud-download"></span>&nbsp;&nbsp;Importer depuis Prodouane</a>
                	<?php endif; ?>
                    <a class="btn btn-xs btn-block btn-default" href="<?php echo ($dr)? url_for('edit_fichier', $dr) : url_for('new_fichier', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'type' => DRClient::TYPE_MODEL)); ?>"><?php echo ($dr)? ($dr->exist('donnees'))? 'Poursuivre les modifications' : 'Modifier la déclaration' : 'Saisir la déclaration'; ?></a>
                </div>
            </div>
    </div>
</div>
<?php endif; ?>
<?php if (class_exists("SV11Client") && class_exists("SV12Client") && ($etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT  || $etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR  || $etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE) && in_array('drev', sfConfig::get('sf_enabled_modules'))): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration de production  <?php echo $campagne; ?></h3>
        </div>
            <div class="panel-body">
                <p>Espace de récupération de la Déclaration de production pour le déclarant.</p>
                <div style="margin-top: 50px; margin-bottom: 26px;">
                  <?php if($sv): ?>
                    <a class="btn btn-block btn-default" href="<?php echo url_for('csvgenerate_fichier', $sv); ?>">Télécharger les données liées à la déclaration</a>
                    <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('upload_fichier', array('sf_subject' => $etablissement));  ?>?fichier_id=<?php echo $sv->_id; ?>"><span class="glyphicon glyphicon-edit"></span>&nbsp;&nbsp;Détails du fichier</a>
                	<?php else: ?>
                    <p class="text-center">Le document n'a pas encore été récupéré pour cette campagne</p>
                	<a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('scrape_fichier', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'type' => ($etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE) ? 'SV11' : 'SV12')) ?>"><span class="glyphicon glyphicon-cloud-download"></span>&nbsp;&nbsp;Importer depuis Prodouane</a>
                	<?php endif; ?>
                </div>
            </div>
    </div>
</div>
<?php endif; ?>
