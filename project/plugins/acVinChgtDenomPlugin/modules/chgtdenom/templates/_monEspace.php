<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Chgt.&nbsp;de&nbsp;dénomination&nbsp;/&nbsp;Déclassement</h3>
        </div>
        <div class="panel-body">
            <?php if ($enCours): ?>
            <p class="explications">Votre déclaration de changement de dénomination / déclassement a été débutée sans avoir été validée.</p>
            <div class="actions">
                <a class="btn btn-block btn-primary" href="<?php echo url_for('chgtdenom_edition', $enCours) ?>"><span class="glyphicon glyphicon-pencil"></span> Reprendre la saisie</a>
                <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-block btn-xs btn-default pull-right" href="<?php echo url_for('chgtdenom_delete', $enCours) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
            </div>
            <?php else: ?>
            <p class="explications">Espace permettant le changement de dénomination ou déclassement de vos logements.</p>
            <div class="actions">
            <a class="btn btn-default btn-block" href="<?php echo url_for('chgtdenom_lots', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class="glyphicon glyphicon-file"></span> <?php echo ($sf_user->isAdmin()) ? 'Saisie papier' : 'Télédéclaration' ?></a>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'chgtdenom')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
