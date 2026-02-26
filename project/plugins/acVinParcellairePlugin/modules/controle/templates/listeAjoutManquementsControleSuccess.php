<ol class="breadcrumb">
    <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
    <li><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee, 'agent_identifiant' => $controle->agent_identifiant)); ?>">Tournée du <?php echo $controle->date_tournee; ?></a></li>
    <li><a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>"><?php echo $controle->declarant->nom ?> (<?php echo $controle->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
    <li class="active"><a href="">Ajouter un manquement</a></li>
</ol>

<h2 class="mb-4">Ajout un manquement</h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error):
            echo $error;
        endforeach; ?>
    </div>
<?php endif;?>

<form id="formAddManquement" action="<?php echo url_for('controle_ajout_liste_manquements', array("id" => $controle->_id)) ?>" method="post" >
    <div class="form-horizontal">
    <div class="form-group">
        <label for="manquementSelect" class="col-sm-3 control-label">
            Choisir un manquement :
        </label>
        <div class="col-sm-7 mb-3">
            <select class="form-control select2 toDuplicate" name="manquement" id="manquementSelect" data-new="ajouter" data-groups='<?php echo json_encode($libellesConstats->getRawValue(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);?>' required>
                <option value=""></option>
            </select>
        </div>
    </div>
    </div>

    <?php $parcellesByCommune = $controle->getParcellaire()->getParcellesByCommune(false);
    $import = $controle->getParcellaire()->getGeoJson(); ?>
    <?php include_partial('parcellaire/tableauCommune', array('parcellesByCommune' => $parcellesByCommune, 'import' => $import, 'addCheckbox' => true)); ?>

    <div class="row">
        <div class="col-sm-6 text-left">
            <a class="btn btn-default" href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
        </div>
        <div class="col-sm-6 text-right">
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
</form>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('manquementSelect');
    const groups = JSON.parse(select.dataset.groups || '{}');

    select.querySelectorAll('optgroup').forEach(el => el.remove());

    Object.entries(groups).forEach(([domaine, types]) => {
        Object.entries(types).forEach(([type, constats]) => {

            if (!constats || Object.keys(constats).length === 0) return;

            const optgroup = document.createElement('optgroup');
            optgroup.label = `${domaine} — ${type}`;

            Object.entries(constats).forEach(([code, libelle]) => {
                const option = document.createElement('option');
                option.value = code;
                option.textContent = `${code} — ${libelle}`;
                optgroup.appendChild(option);
            });

            select.appendChild(optgroup);
        });
    });

    $('#manquementSelect').select2({
        placeholder: 'Rechercher un manquement',
        allowClear: true,
        width: '100%',
        language: {
            noResults: () => "Aucun manquement trouvé"
        }
    });
});
</script>
