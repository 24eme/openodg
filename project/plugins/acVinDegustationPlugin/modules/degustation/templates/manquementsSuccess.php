<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des manquements à traiter</h2>
</div>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th>Déclarant</th>
    <th>Numéro de dossier</th>
    <th>Appellation</th>
    <th>Volume</th>
    <th>type de manquement</th>
    <th>Motif</th>
    <th>Observation</th>
    <th>Action</th>
</thead>
<tbody>
<?php foreach($manquements as $m): ?>
    <tr>
        <td><?php echo $m->declarant_nom; ?></td>
        <td></td>
        <td><?php echo $m->produit_libelle." ".$m->millesime; ?></td>
        <td><?php echo $m->volume; ?></td>
        <td><?php echo Lot::$libellesConformites[$m->conformite]; ?></td>
        <td><?php echo $m->motif; ?></td>
        <td><?php echo $m->observation; ?></td>
        <td>
            <div class="dropdown">
              <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                &nbsp;
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li><a class="dropdown-item" href="#">Redéguster</a></li>
                <li><a class="dropdown-item" href="<?php echo url_for('degustation_etablissement_list', array('id' => $m->declarant_identifiant)) ?>">Voir l'historique</a></li>
                <li><a class="dropdown-item" href="#">Clore</a></li>
            </ul>
              </div>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
