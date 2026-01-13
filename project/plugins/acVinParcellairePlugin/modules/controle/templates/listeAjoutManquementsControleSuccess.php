<h2>Ajout un manquement</h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<div class="container col-xs-12">
    <form id="formAddManquement" action="<?php echo url_for('controle_ajout_liste_manquements', array("id" => $controle->_id)) ?>" method="post">
        <label for="manquementSelect">Choisir un manquement :</label>
        <select name="manquement" id="manquementSelect">
            <option value=""></option>
            <?php foreach ($listeManquements as $idRtm => $manquement): ?>
                <option value="<?php echo $idRtm ?>"><?php echo $manquement ?></option>
            <?php endforeach;?>
        </select>

        <button type="submit" class="btn btn-success pull-right">Ajouter</button>
    </form>
</div>
