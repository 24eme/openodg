<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Tables</h3>
  </div>
  <ul class="list-group" id="liste-tables">
    <?php $lastli = ($numero_table && $degustation->hasFreeLots()) ? [count($degustation->getTables()->getRawValue()) + 1 => []] : []; ?>
    <?php foreach ($degustation->getTables()->getRawValue() + $lastli as $table => $lots): ?>
        <a href="<?php echo url_for('degustation_organisation_table', ['id' => $degustation->_id, 'numero_table' => $table]) ?>"
            data-table="<?php echo $table ?>"
            class="list-group-item<?php if ($numero_table == $table): echo " active" ; endif ?>">
            <span class="badge"><?php echo count($lots) ?></span>
            Table <?php echo DegustationClient::getNumeroTableStr($table) ?>
        </a>
    <?php endforeach ?>
    <a href="<?php echo url_for('degustation_organisation_table_recap', ['id' => $degustation->_id]) ?>"
        class="list-group-item<?php if ($numero_table == null): echo " active" ; endif ?>">
        Récapitulatif
    </a>
  </ul>
</div>
