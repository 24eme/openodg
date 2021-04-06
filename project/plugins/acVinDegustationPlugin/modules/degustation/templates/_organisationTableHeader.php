<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TABLES)); ?>
<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Attribution des échantillons aux tables</h2>
</div>

<?php $liste_tables = $degustation->getTablesWithFreeLots(); ?>
<?php $numero_table = isset($numero_table) ? $numero_table : false; ?>

<ul class="nav nav-pills degustation">
  <?php for ($i= 0; $i < count($liste_tables); $i++): ?>
    <li role="presentation" class="ajax <?php if($numero_table == ($i + 1)): echo "active"; endif; ?>">
        <a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => ($i + 1), 'tri' => $tri)) ?>">Table <?php echo DegustationClient::getNumeroTableStr($i + 1); ?></a>
    </li>
  <?php endfor;?>
  <?php if( $numero_table > count($liste_tables)): ?>
    <li role="presentation" class="active"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $numero_table, 'tri' => $tri)) ?>">Table <?php echo DegustationClient::getNumeroTableStr($numero_table); ?></a></li>
  <?php endif; ?>
  <li role="presentation"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => count($liste_tables)+1, 'tri' => $tri)) ?>"><span class="glyphicon glyphicon-plus"></span></a></li>
  <li role="presentation" class="<?php if(!$numero_table): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_organisation_table_recap", array('id' => $degustation->_id, 'tri' => $tri)) ?>"><span class="glyphicon glyphicon-th-list"></span> Récapitulatif</a></li>
</ul>
