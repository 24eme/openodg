<ol class="breadcrumb">
    <li>Certipaq</li>
    <li>Liste des demandes</li>
</ol>


<div class="page-header no-border">
    <h1>Certipaq : liste des demandes</h1>
</div>

<table class="table table-bordered table-striped">
<thead><tr>
    <th>Identifiant de la demande</th>
    <th>Date de communication</th>
    <th>Opérateur</th>
    <th>Type</th>
    <th>Etat</th>
</tr></thead>
<tbody>
<?php foreach($param_printable as $id => $demande): ?>
<tr>
    <td class="text-center"><a href="<?php echo url_for('certipaq_demande_identification_view', array('request_id' => $id)); ?>"><?php echo $id; ?></a></td>
    <td class="text-right"><?php echo $demande['date_ajout']; ?></td>
    <td><?php if (isset($demande['operateur'])) { echo $demande['operateur']->raison_sociale; } ?></td>
    <td><?php echo $demande['dr_demandeentification_type']; ?></td>
    <td><a href="<?php echo url_for('certipaq_demande_identification_view', array('request_id' => $id)); ?>"><?php echo $demande['dr_etat_demande']->libelle; ?></a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<pre>
<?php print_r($param); ?>
</pre>