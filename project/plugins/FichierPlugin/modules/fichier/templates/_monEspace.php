<?php use_helper('Date'); ?>

<?php if (!$sf_user->isAdminODG() && DRConfiguration::getInstance()->hasVisuTeledeclaration() === false): ?>
    <?php return; ?>
<?php endif; ?>

<?php if (!isset($type) || !$type): ?>
    <?php return; ?>
<?php endif; ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($declaration):?>panel-success<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?php echo $typeLibelle ?> <?php echo $periode; ?><?php if ($sf_user->isAdminODG() && $declaration && $declaration->isValideeOdg() ): ?> <span class="pull-right"><span class="glyphicon glyphicon-ok-circle"></span></span> <?php endif; ?>
            </h3>
        </div>
        <div class="panel-body">
            <p class="explications">
            <?php if($declaration): ?>
                Votre déclaration a été récupérée depuis prodouane.
            <?php else: ?>
                Votre déclaration n'a pas encore été récupérée. Pour la récupérer il vous suffit de démarrer la télédéclaration de votre déclaration de Revendication.
            <?php endif; ?>
            </p>
            <div class="actions">
                <?php if($declaration): ?>
                    <a class="btn btn-block btn-default" href="<?php echo url_for('dr_visualisation', $declaration) ?>">Visualiser la synthèse du document</a>
                <?php elseif($sf_user->isAdminODG()): ?>
                    <a class="btn btn-default btn-block" href="<?php echo url_for('scrape_fichier', array('sf_subject' => $etablissement, 'periode' => $periode, 'type' => $type)) ?>"><span class="glyphicon glyphicon-cloud-download"></span>&nbsp;&nbsp;Importer depuis Prodouane</a>
            	<?php endif; ?>
                <?php if($sf_user->isAdminODG() && $declaration && $declaration->type == DRClient::TYPE_MODEL): ?>
                    <a class="btn btn-xs btn-block btn-default" style="opacity: 0.75;" href="<?php echo url_for('edit_fichier', $declaration) ?>"><span class="glyphicon glyphicon-pencil"></span> Modifier la déclaration</a>
                <?php elseif($sf_user->isAdminODG() && !$type == DRClient::TYPE_MODEL): ?>
                    <a class="btn btn-xs btn-block btn-default"  style="opacity: 0.75;" href="<?php echo url_for('new_fichier', array('sf_subject' => $etablissement, 'periode' => $periode, 'type' => DRClient::TYPE_MODEL)); ?>"><span class="glyphicon glyphicon-pencil"></span> Saisir la déclaration</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => strtolower($type))) ?>" class="btn btn-xs btn-link btn-block text-muted">Voir tous les documents</a>
        </div>
    </div>
</div>
