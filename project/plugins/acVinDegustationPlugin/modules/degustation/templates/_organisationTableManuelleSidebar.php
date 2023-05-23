<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Tables</h3>
  </div>
  <ul class="list-group" id="liste-tables">
    <?php foreach ($degustation->getTables()->getRawValue() + [count($degustation->getTables()->getRawValue()) + 1 => []] as $table => $lots): ?>
        <a href="<?php echo url_for('degustation_organisation_table', ['id' => $degustation->_id, 'numero_table' => $table]) ?>"
            data-table="<?php echo $table ?>"
            class="list-group-item<?php if ($numero_table == $table): echo " active" ; endif ?>">
            <span class="badge"><?php echo count($lots) ?></span>
            Table <?php echo DegustationClient::getNumeroTableStr($table) ?>
        </a>
    <?php endforeach ?>
  </ul>
</div>
