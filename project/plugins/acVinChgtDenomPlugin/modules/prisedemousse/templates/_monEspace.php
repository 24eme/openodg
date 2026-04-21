<?php if (!ChgtDenomConfiguration::getInstance()->isDematEnabled()) return false; ?>
<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Prise de Mousse</h3>
        </div>
        <div class="panel-body">
            <?php if ($enCours): ?>
            <p class="explications">Votre déclaration de prise de mousse a été débutée sans avoir été validée.</p>
            <div class="actions">
                <a class="btn btn-block btn-primary" href="<?php echo url_for('prisedemousse_edition', $enCours) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('prisedemousse_delete', $enCours) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
            </div>
            <?php else: ?>
            <p class="explications">Espace permettant la déclaration de prise de mousse pour vos lot en vin de base.</p>
            <div class="actions">
            <a class="btn btn-default btn-block" href="<?php echo url_for('prisedemousse_lots', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><?php if($sf_user->isAdmin()): ?><span class="glyphicon glyphicon-file"></span> Saisie papier<?php else: ?>Démarrer la télédéclaration<?php endif; ?></a>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'prisedemousse')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
