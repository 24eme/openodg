<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="">Tournée du <?php echo Date::francizeDate($dateTournee); ?></a></li>
  <li class="active"><a href="">Liste des opérateurs contrôlés</a></li>
</ol>

<h2>Opérateurs controlés dans la tournée du <?php echo Date::francizeDate($dateTournee); ?></h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-xs-6">Opérateurs</th>
        <th class="col-xs-2">Type de tournée</th>
        <th class="col-xs-4" colspan="2">PDF</th>
        <th style="width: 0;"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($controles as $id => $data): ?>
        <tr>
            <td>
                <a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $data->_id)) ?>"><?php echo $data->declarant->raison_sociale ?></a>
            </td>
            <td><?php echo $data->type_tournee; ?></td>
            <td><a href="<?php echo url_for('controle_pdf', array('id' => $data->_id)); ?>">PDF du contrôle</a></td>
            <td><a href="<?php echo url_for('controle_pdf_manquements', array('id' => $data->_id)); ?>">PDF des manquements</a></td>
            <td class="text-right"><a class="btn btn-xs btn-default" href="<?php echo url_for('controle_liste_manquements_controle', array('id' => $data->_id)); ?>">Gérer les manquements</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-xs-4"><a class="btn btn-default" href="<?php echo url_for('controle_index'); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
</div>
