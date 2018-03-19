<?php use_helper('Date'); use_helper('Float');
 include_partial('registrevci/breadcrumb', array('registre' => $registre )); ?>
<div class="page-header no-border">
    <h2>Registre VCI <?php echo $registre->campagne; ?> <small class="pull-right"></small></h2>
</div>
<?php if (!$registre->getDRev()): ?>
  <div class="alert alert-danger" role="alert">Pas de DRev <?php echo $registre->campagne; ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-xs-12">
        <h3>Résumé du registre VCI</h3>
        <table class="table table-striped">
        <thead><tr>
            <th class="col-md-4">Produit</th>
            <th class="text-center col-md-1">Constiuté</th>
        	  <th class="text-center col-md-1">Rafraîchi</th>
            <th class="text-center col-md-1">Complément</th>
            <th class="text-center col-md-1">Substitution</th>
            <th class="text-center col-md-1">Destruction</th>
            <th class="text-center col-md-1">Stock</th>
        </tr></thead>
<?php foreach ($registre->getProduitsWithPseudoAppelations() as $p) :
  $strongbegin = ''; $strongend = ''; $superficiebegin = ''; $superficieend = '';
  if ($p->isPseudoAppellation()) {
    $strongbegin = '<strong>';
    $strongend = '</strong>';
    $superficiebegin = '<small class="text-mutted">';
    $superficieend = ' ('.$p->getSuperficieFromDrev().' hl)</small>';
  }
  ?>
      <tr>
          <td><?php echo $strongbegin.$p->libelle.$strongend.$superficiebegin.$superficieend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->constitue).$strongend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->rafraichi).$strongend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->complement).$strongend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->substitution).$strongend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->destruction).$strongend; ?></td>
          <td class="text-right"><?php echo $strongbegin.formatFloat($p->stock_final).$strongend; ?></td>
      </tr>
<?php endforeach; ?>
</table>
</div></div>
<div class="row">
    <div class="col-xs-12">
        <h3>Détails du registre VCI</h3>
        <table class="table table-striped">
        <thead><tr>
            <th class="col-md-3">Produit</th>
            <th class="col-md-1">Date</th>
        	  <th class="col-md-2">Lieu</th>
            <th class="col-md-1">Type de mvmt</th>
            <th class="col-md-1">Volume</th>
            <th class="col-md-1">Stock résultant</th>
        </tr></thead>
<?php foreach ($registre->mouvements as $i => $d): ?>
      <tr>
          <td><?php echo $d->produit_libelle; ?></td>
          <td><?php echo format_date($d->date); ?></td>
          <td><?php echo $d->detail_libelle; ?></td>
          <td><?php echo RegistreVCIClient::MOUVEMENT_LIBELLE($d->mouvement_type); ?></td>
          <td class="text-right"><?php echo echoFloat($d->volume); ?></td>
          <td class="text-right"><?php echo echoFloat($d->stock_resultant); ?></td>
      </tr>
<?php endforeach; ?>
      </table>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <h3>Superficies facturables</h3>
        <ul>
          <p><strong><?php echo $registre->superficies_facturables; ?> are(s)</strong> de superficies facturables</p>
        </ul>
</div></div>
<div class="row row-margin row-button">
    <div class="col-xs-5">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $registre->identifiant, 'campagne' => $registre->campagne)); ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
</div>
