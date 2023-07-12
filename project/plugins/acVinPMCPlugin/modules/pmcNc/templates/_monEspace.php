<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Mise en circulation suite à non conformité</h3>
        </div>
        <div class="panel-body">
            <p class="explications">Espace permettant la déclaration de vos mises en circulation suite à une non conformité.</p>
            <div class="actions">
            <a class="btn btn-default btn-block" href="<?php echo url_for('pmcnc_lots', array('sf_subject' => $etablissement)) ?>"><?php if($sf_user->isAdmin()): ?><span class="glyphicon glyphicon-file"></span> Saisie papier<?php else: ?>Démarrer la télédéclaration<?php endif; ?></a>
            </div>
        </div>
        <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
            <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'pmcNc')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
        </div>
    </div>
</div>
