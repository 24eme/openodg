<?php use_helper('Date'); ?>

<?php if ($pmc || $sf_user->isAdmin() || $sf_user->hasConditionnementAdmin() || EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_PMC)): ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($pmc && $pmc->validation): ?>panel-success<?php elseif($pmc): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration de mise en circulation</h3>
        </div>
        <?php if ($pmc && $pmc->validation): ?>
            <div class="panel-body">
                <p class="explications">Votre déclaration de mise en circulation a été validée aujourd'hui.</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('pmc_visualisation', $pmc) ?>">Visualiser la déclaration</a>
                </div>
            </div>
        <?php elseif ($pmc): ?>
            <div class="panel-body">
                <p class="explications">Reprendre la déclaration de mise en circulation du <?php echo $pmc->getDateFr(); ?>.</p>
                <div class="actions">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('pmc_edit', $pmc) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('pmc_delete', $pmc) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p class="explications">Espace permettant la déclaration de vos mises en circulation.</p>
                <div class="actions">
                    <?php if ($sf_user->isAdmin() || $sf_user->hasPMCAdmin()): ?>
                        <a class="btn btn-block btn-default" href="<?php echo url_for('pmc_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php else: ?>
                        <a class="btn btn-block btn-default" href="<?php echo url_for('pmc_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'pmc')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
<?php endif ?>
