<ol class="breadcrumb">
    <li>Certipaq</li>
    <li><a href="<?php echo url_for('certipaq_list_demandes'); ?>">Demandes</a></li>
    <li>Détail de la demande <?php echo $id; ?></li>
</ol>

<div class="page-header no-border">
    <h1>Certipaq : Détail de la demande <?php echo $id; ?></h1>
</div>
<div class="row">
<h2>Demande</h2>
<table class="table">
<?php foreach ($param_printable as $k => $value) : ?>
<tr><th><?php echo $k ; ?></th><td><?php echo $value; ?></td></tr>
<?php endforeach; ?>
</table>
</div>
<?php if (isset($demande)): ?>
<div class="row">
<a href="<?php echo url_for('habilitation_demande_edition', array('identifiant' => $habilitation->identifiant, 'demande'=> $demande->getKey())); ?>" class="btn btn-default">Retour à la demande</a>
<?php if($param['dr_etat_demande_id'] == 4): ?>
<a href="<?php echo url_for('certipaq_demande_identification_documents', array('request_id' => $id, 'identifiant' => $habilitation->identifiant, 'demande' => $demande->getKey())); ?>" class="btn btn-success pull-right">Reprendre la transmission</a>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="row">
<h2>Raw</h2>
<pre>
<?php print_r($param); ?>

Décisions :
<?php print_r($decisions); ?>
</pre>
</div>
