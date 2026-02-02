<h2>Opérateurs controlés dans la tournée du <?php echo Date::francizeDate($dateTournee); ?> </h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-6"></th>
        <th class="col-6"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($controles as $id => $data): ?>
        <tr>
            <td>
                <a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $data->_id)) ?>"><?php echo $data->declarant->raison_sociale ?></a>
            </td>
            <td><a href="<?php echo url_for('controle_pdf_manquements', array('id' => $data->_id)); ?>">Télécharger le PDF des manquements</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
