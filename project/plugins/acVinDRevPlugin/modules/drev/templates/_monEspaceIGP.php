<?php use_helper('Date'); ?>

<?php if ($drev || $sf_user->isAdmin() || $sf_user->hasDrevAdmin() || EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_DREV)): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($drev): ?>panel-primary<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Revendication <?php echo $periode; ?></h3>
        </div>
        <div class="panel-body">
            <p>Espace permettant de revendiquer vos lots en IGP et consulter ceux que vous avez déjà revendiqué</p>
            <div style="margin-top: 76px;">
                <?php if($drev && $drev->validation): ?>
                <a class="btn btn-block btn-primary" href="<?php echo           url_for('drev_visualisation', $drev) ?>">Consulter et Revendiquer</a>
                <?php elseif($drev && !$drev->validation): ?>
                    <a class="btn btn-block btn-primary" href="<?php echo           url_for('drev_edit', $drev) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('drev_delete', $drev) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                <?php else: ?>
                    <a class="btn btn-default btn-block" href="<?php echo url_for('drev_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin()): ?>
                    <a class="btn btn-xs btn-default btn-block" href="<?php echo url_for('drev_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'drev')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
<?php endif; ?>
