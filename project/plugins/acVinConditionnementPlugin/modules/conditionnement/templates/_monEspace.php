<?php use_helper('Date'); ?>

<?php if ($conditionnement || $sf_user->isAdmin() || $sf_user->hasConditionnementAdmin() || EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_CONDITIONNEMENT)): ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($conditionnement && $conditionnement->validation): ?>panel-success<?php elseif($conditionnement): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration&nbsp;de&nbsp;Conditionnement</h3>
        </div>
        <?php if ($conditionnement && $conditionnement->validation): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration de conditionnement a été validée aujourd'hui.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_visualisation', $conditionnement) ?>">Visualiser la déclaration</a>
                </div>
            </div>
        <?php elseif ($conditionnement): ?>
            <div class="panel-body">
                <p class="explications">Reprendre la déclaration de conditionnement du <?php echo format_date($conditionnement->getDate(), 'dd/MM/yyyy'); ?>.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('conditionnement_edit', $conditionnement) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('conditionnement_delete', $conditionnement) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p class="explications">Espace permettant la déclaration de vos conditionnements.</p>
                <div class="actions">
                    <?php if ($sf_user->isAdmin() || $sf_user->hasConditionnementAdmin()): ?>
                        <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php else: ?>
                        <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'conditionnement')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
<?php endif ?>
