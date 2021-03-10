<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_CONDITIONNEMENT) && (!$conditionnement || !$sf_user->isAdmin() || !$sf_user->hasCondtionnementAdmin())): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($conditionnement && $conditionnement->validation): ?>panel-success<?php elseif($conditionnement): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration&nbsp;de&nbsp;Conditionnement</h3>
        </div>
        <?php if ($conditionnement && $conditionnement->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration de conditionnement a été validée aujourd'hui.</p>
                <div style="margin-top: 76px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_visualisation', $conditionnement) ?>">Voir le Conditonnement</a>
                </div>
            </div>
        <?php elseif ($conditionnement): ?>
            <div class="panel-body">
                <p>Reprendre la déclaration de conditionnement du <?php echo format_date($conditionnement->getDate(), 'dd/MM/yyyy'); ?>.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('conditionnement_edit', $conditionnement) ?>"><?php if($conditionnement->isPapier()): ?><span class="glyphicon glyphicon-file"></span> Reprendre le conditionnement papier<?php else: ?>Reprendre la télédéclaration<?php endif; ?></a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('conditionnement_delete', $conditionnement) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p>Espace permettant la déclaration de vos conditionnements.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('conditionnement_create', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasConditionnementAdmin()): ?>
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('conditionnement_create_papier', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisir le conditionnement papier</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
