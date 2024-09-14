<?php use_helper('Date'); ?>

<?php include_partial('global/flash'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $dr->identifiant, 'campagne' => $dr->campagne)); ?>"><?php echo $dr->getEtablissementObject()->getNom() ?> (<?php echo $dr->getEtablissementObject()->identifiant ?> - <?php echo $dr->getEtablissementObject()->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('dr_visualisation', array('id' => $dr->_id)); ?>"><?php if($dr->isBailleur()) echo "Synthèse bailleur "; else echo $dr->type; ?> de <?php echo $dr->getperiode(); ?></a></li>
  <li class="active"><a href="">Vérification</a></li>
</ol>

<div class="page-header no-border clearfix">
    <h2>Tableau de vérification <?php echo $dr->type; ?> <?= $dr->campagne ?></h2>
</div>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $dr->getEtablissementObject()]); ?>
</div>

<?php use_helper('Float') ?>

<h3>Comparaison des superficies entre la DAP et la DR <?php echo $dr->campagne; ?></h3>
<?php include_component('dr', 'tableComparatif', array('tableau_comparaison' => $tableau_comparaison)); ?>



<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?= (isset($service) && $service) ?: url_for('dr_visualisation', array('id' => $dr->_id)); ?>"
            class="btn btn-default"
            >
            <i class="glyphicon glyphicon-chevron-left"></i> Retour
        </a>
    </div>
</div>
