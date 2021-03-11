<?php use_helper('Float'); ?>
<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des manquements à traiter</h2>
</div>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th>Déclarant</th>
    <th class="text-center">Numéro de dossier</th>
    <th>Appellation</th>
    <th>Volume</th>
    <th>Manquement</th>
    <th>Action</th>
</thead>
<tbody>
<?php foreach($manquements as $keyLot => $m): ?>
    <tr>
        <td><?php echo $m->declarant_nom; ?></td>
        <td class="text-center"><?php echo $m->numero_dossier; ?></td>
        <td><?php echo $m->produit_libelle." ".$m->millesime; ?></td>
        <td class="text-right"><?php echo formatFloat($m->volume); ?>&nbsp;hl</td>

        <td><?php echo $m->conformite?Lot::$libellesConformites[$m->conformite]: null; ?> <span class="text-muted"><?php echo $m->motif; ?></span></td>
        <td>
            <div class="dropdown">
              <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                &nbsp;
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li><a class="dropdown-item" href="<?php echo url_for('degustation_redeguster', array('id' => $m->id_document, 'index' => str_replace('/lots/', '', $m->origine_hash), 'back' => 'degustation_manquements')) ?>" onclick="return confirm('Confirmez vous de rendre dégustable à nouveau ce lot ?')">Redéguster</a></li>
                <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_lot', array('identifiant' => $m->declarant_identifiant, 'lot' => $m->origine_document_id.":".$m->origine_mouvement)) ?>">Déclassement / Chgmt denom.</a></li>
                <li><a class="dropdown-item" href="<?php echo url_for('degustation_etablissement_list', array('id' => $m->declarant_identifiant)) ?>">Voir l'historique</a></li>
            </ul>
              </div>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
