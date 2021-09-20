<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <div class="pull-right">
      <?php if ($sf_user->hasDrevAdmin()): ?>
      <form method="GET" class="form-inline" action="">
          Campagne :
          <select class="select2SubmitOnChange form-control" name="campagne">
              <option value="">Toutes</option>
              <?php for($i=ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent(); $i > ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() - 5; $i--): ?>
                  <option <?php if(intval($campagne) == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i; ?>-<?php echo $i+1 ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
              <?php endfor; ?>
          </select>
          <button type="submit" class="btn btn-default">Changer</button>
      </form>
      <?php else: ?>
          <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?></span>
      <?php endif; ?>
    </div>
    <h2>Liste des manquements à traiter</h2>
</div>

<div class="row">
    <div class="form-group col-xs-10">
      <input id="hamzastyle" type="hidden" data-placeholder="Sélectionner un filtre" data-hamzastyle-container=".table_manquements" data-hamzastyle-mininput="3" class="select2autocomplete hamzastyle form-control">
    </div>
</div>

<br/>
<?php include_partial('degustation/lots', array('lots' => $manquements)); ?>
<?php use_javascript('hamza_style.js'); ?>
