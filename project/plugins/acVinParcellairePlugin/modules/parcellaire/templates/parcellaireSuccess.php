<?php use_javascript('hamza_style.js?20230328'); ?>
<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php
$parcellaire_client = ParcellaireClient::getInstance();
$last = null;
$list_communes = [];
$list_idu = [];
?>

<ol class="breadcrumb">
<?php if($sf_user->hasTeledeclaration()): ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
<?php else: ?>
    <li><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
<?php endif; ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire de <?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>) </a></li>
  <?php if($parcellaire): ?>
  <li><span class="text-muted"><?php echo $parcellaire->_id; ?></span></li>
  <?php endif; ?>
</ol>

<?php if ($sf_user->isAdmin() && class_exists("EtablissementChoiceForm") && isset($form)): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('parcellaire_etablissement_selection'), 'noautofocus' => true)); ?>
<?php endif; ?>

<div class="page-header no-border row">
    <?php if($parcellaire): ?>
    <h2 class="col-xs-8">Parcellaire au <?php echo Date::francizeDate($parcellaire->date); ?> <small class="text-muted"><?= $parcellaire->source ?></small></h2>
    <?php else: ?>
    <h2 class="col-xs-8">Parcellaire</h2>
    <?php endif;?>
    <p class="col-xs-4 text-muted p-4 mt-4 text-right"><?= $parcellaire->_id ?></p>
</div>

<div class="clearfix">
  <a href="<?= url_for('parcellaire_scrape_douane', $etablissement) ?>" class="btn btn-warning pull-right" style="margin-bottom: 10px;">
      <i class="glyphicon glyphicon-refresh"></i> Mettre à jour via Prodouane
  </a>
</div>

<?php include_partial('global/flash'); ?>

<?php if($parcellaire): ?>
    <div class="well">
        <?php include_partial('etablissement/blocDeclaration', array('etablissement' => $etablissement)); ?>
    </div>
<?php endif; ?>


<?php if ($parcellaire && $parcellaire->hasParcelles()): ?>
    <?php $parcellesByCommune = $parcellaire->getParcellesByCommune(false);
    $import = $parcellaire->getGeoJson(); ?>

    <?php if($parcellaire && $parcellaire->getGeoJson() != false): ?>
        <div id="jump">
            <a name="carte"></a>
            <?php include_partial('parcellaire/parcellaireMap', array('parcellaire' => $parcellaire)); ?>
        </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-xs-5">
          <h3>Accès rapide</h3>
          <ul>
            <?php foreach (array_keys($parcellesByCommune->getRawValue()) as $commune): ?>
              <li style="list-style-type: disclosure-closed"><a href="#parcelles_<?php echo $commune ?>">Parcelles de <?php echo ucwords(strtolower($commune), "- \t\r\n\f\v") ?></a></li>
            <?php endforeach ?>
            <li style="list-style-type: disclosure-closed"><a href="#synthese_cepage">Synthèse par cépages</a></li>
            <li style="list-style-type: disclosure-closed"><a href="#synthese_produit">Synthèse par produits</a></li>
          </ul>
      </div>
    </div>

<?php include_partial('parcellaire/tableauCommune', array('parcellesByCommune' => $parcellesByCommune, 'import' => $import, 'addCheckbox' => false)); ?>


<?php include_component('parcellaire', 'syntheseParCepages', array('parcellaire' => $parcellaire)); ?>
<?php
    $potentiel = null;
    if($parcellaire) {
        $potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($parcellaire->getRawValue());
    }
    if ($potentiel):
?>
<h3 id="synthese_produit">
    Synthèse par produits habilités
<?php if (ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration()): ?>
    et reconnus au CVI
<?php endif; ?>
</h3>

<?php if (! HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant)): ?>
    <div class="alert alert-warning" role="alert">
    L'opérateur n'a pas d'<a href="<?php echo url_for('habilitation_declarant', ['identifiant' => $etablissement->identifiant]) ?>">habilitation</a>
    </div>
<?php endif ?>

<table class="table table-bordered table-condensed table-striped tableParcellaire">
  <thead>
    <tr>
        <th class="col-xs-3">Produit</th>
        <th class="col-xs-8">Cépages autorisés <small class="text-muted">(hors <abbr title="de la campagne <?php echo ParcellaireConfiguration::getInstance()->getCampagneJeunesVignes(); ?> à <?php echo ConfigurationClient::getInstance()->getCampagneParcellaire()->getCurrent(); ?>">jeunes vignes</abbr>)</small></th>
        <th class="col-xs-1 text-center">Superficie Pot. max. <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
        <th class="col-xs-1 text-center">Encépa- gement <span class="text-muted small"><?php echo (ParcellaireConfiguration::getInstance()->isAres()) ? "(a)" : "(ha)" ?></span></th>
    </tr>
  </thead>
  <tbody>
<?php
    $cepages_autorises = [];
    $has_affectation = false;
    foreach($potentiel->getProduits() as $ppproduit): ?>
            <tr>
                <td>
                    <?php echo $ppproduit->getLibelle(); ?>
                    <?php if ($ppproduit->parcellaire2refIsAffectation()) { echo ' <b>*</b> '; $has_affectation = true; } ?>
                </td>
                <td><?php echo implode(', ', $ppproduit->getCepages()); ?></td>
                <td class="text-right<?php if ($ppproduit->hasSuperificieMax() && $ppproduit->hasLimit()) { if ($ppproduit->getSuperficieMax() > 0) { echo " warning"; } else {echo " danger"; } } ?>"><?php if ($ppproduit->hasSuperificieMax()) echoSuperficie($ppproduit->getSuperficieMax()); ?></td>
                <td class="text-right<?php if (!$ppproduit->hasLimit()) { echo " success"; } ?>"><?php echoSuperficie($ppproduit->getSuperficieEncepagement()); ?></td>
            </tr>
<?php endforeach; ?>
  </tbody>
</table>

<?php
$a = $potentiel->getParcellaireAffectation();
if ($has_affectation) :
?>
<p><b>*</b> : Pour ce produit, les superficies sont issues de l'<a href="<?php echo url_for('parcellaireaffectation_visualisation', $a)?>">affectation</a> et non du parcellaire.</p>
<?php endif; ?>
<?php if ($potentiel->hasPotentiels()): ?>
<p>
    <a href="<?php echo url_for('parcellaire_potentiel_visualisation', array('id' => $parcellaire->_id)); ?>">Voir le détail du potentiel du production</a>
    qui a été calculé d'après le parcellaire<?php if ($a):?> et l'<a href="<?php echo url_for('parcellaireaffectation_visualisation', $a)?>">affectation parcellaire</a><?php endif; ?>.
</p>
<?php endif; ?>

<?php endif; ?>
<?php else: ?>
    <div class="row" style="min-height: 370px;">
        <div class="col-xs-12 text-center">
            <p>Aucune parcellaire n'existe pour <?php echo $etablissement->getNom() ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($parcellaire): ?>
<hr/>
<?php
    //Permet aux différentes régions d'avoir des liens personnalisables
    include_partial('downloadLinks', array('parcellaire' => $parcellaire));
?>
<?php endif; ?>

<?php if($sf_user->hasTeledeclaration()): ?>
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
</div>
<?php endif;?>
<script type="text/javascript">
    var all_idu = JSON.parse('<?php echo json_encode(($list_idu)); ?>');
</script>
