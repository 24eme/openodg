

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Attribution des tables</h2>
</div>

<ul class="nav nav-pills degustation">
  <li role="presentation" class="<?php if(!$numero_table): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => 0)) ?>">Toutes tables</a></li>
  <?php for ($i= 0; $i < $nb_tables; $i++): ?>
    <li role="presentation" class="ajax <?php if($numero_table == ($i + 1)): echo "active"; endif; ?>"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => ($i + 1))) ?>">Table <?php echo ($i + 1); ?></a></li>
  <?php endfor;?>
  <?php if( $numero_table > $nb_tables): ?>
    <li role="presentation" class="active"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $numero_table)) ?>">Table <?php echo $numero_table; ?></a></li>
  <?php endif; ?>
  <li role="presentation"><a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $nb_tables+1)) ?>">+ table</a></li>
</ul>

<div class="row row-condensed">
	<div class="col-xs-12">

<?php if(!$numero_table): ?>
	<?php include_partial('degustation/organisationMultiTables', array('degustation' => $degustation,'form' => $form, 'ajoutLeurreForm' => $ajoutLeurreForm)); ?>
<?php else: ?>
	<?php include_partial('degustation/organisationOneTable', array('degustation' => $degustation,'form' => $form,'numero_table' => $numero_table)); ?>
<?php endif; ?>

  </div>
</div>
