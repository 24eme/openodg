<?php use_helper('Date'); ?>

<?php include_partial('global/flash'); ?>

<div class="page-header no-border clearfix">
    <h2>
        Déclaration de Récolte <?= $dr->campagne ?>
        <small class="pull-right">
            <i class="glyphicon glyphicon-file"></i>
            Déclaration importé le <?= format_date($dr->date_import, "dd/MM/yyyy", "fr_FR") ?>
        </small>
    </h2>
</div>

<?php if($dr->exist('validation') && $dr->validation): ?>
    <div class="text-right text-success">
        DR validée le <?= format_date($dr->validation, "dd/MM/yyyy", "fr_FR") ?>
    </div>
<?php endif ?>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $dr->getEtablissementObject()]); ?>
</div>

<?php include_partial('dr/recap', compact('dr', 'lignesAAfficher')) ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?= ($service) ?: url_for('declaration_etablissement', ['identifiant' => $dr->identifiant, 'campagne' => $dr->campagne]) ?>"
            class="btn btn-default"
        >
            <i class="glyphicon glyphicon-chevron-left"></i> Retour
        </a>
    </div>

    <div class="col-xs-4 text-center">
        <a class="btn btn-default" href="#">
            <i class="glyphicon glyphicon-file"></i> DR
        </a>
    </div>

    <div class="col-xs-4 text-right">
        <?php if($dr->exist('validation') && $dr->validation): ?>
            DR approuvée le <?= format_date($dr->validation, "dd/MM/yyyy", "fr_FR") ?>
        <?php else : ?>
            <a href="<?= url_for('dr_approbation', ['id' => $dr->_id]) ?>" class="btn btn-default">
                Approuver la DR
            </a>
        <?php endif ?>
    </div>
</div>
