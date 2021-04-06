<?php use_helper('Date'); ?>

<?php if (!EtablissementSecurity::getInstance($sf_user, $etablissement->getRawValue())->isAuthorized(EtablissementSecurity::DECLARANT_TRANSACTION) && (!$transaction || !$sf_user->isAdmin() || !$sf_user->hasCondtionnementAdmin())): ?>
    <?php return; ?>
<?php endif; ?>

<?php if ($campagne !== ConfigurationClient::getInstance()->getCampagneVinicole()->getCurrent()): ?>
    <?php return ?>
<?php endif ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if ($transaction && $transaction->validation): ?>panel-success<?php elseif($transaction): ?>panel-primary<?php else : ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration&nbsp;de&nbsp;Vrac export</h3>
        </div>
        <?php if ($transaction && $transaction->validation): ?>
            <div class="panel-body">
                <p>Votre déclaration de vrac export a été validée aujourd'hui.</p>
                <div style="margin-top: 76px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('transaction_visualisation', $transaction) ?>">Visualiser la déclaration</a>
                </div>
            </div>
        <?php elseif ($transaction): ?>
            <div class="panel-body">
                <p>Reprendre la déclaration de vrac export du <?php echo format_date($transaction->getDate(), 'dd/MM/yyyy'); ?>.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-primary" href="<?php echo url_for('transaction_edit', $transaction) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                    <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('transaction_delete', $transaction) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
                </div>
            </div>
        <?php else: ?>
            <div class="panel-body">
                <p>Espace permettant la déclaration de vos vrac exports.</p>
                <div style="margin-top: 50px;">
                    <a class="btn btn-block btn-default" href="<?php echo url_for('transaction_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la télédéclaration</a>
                    <?php if ($sf_user->isAdmin() || $sf_user->hasTransactionAdmin()): ?>
                        <a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('transaction_create_papier', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Saisie papier</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'transaction')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
