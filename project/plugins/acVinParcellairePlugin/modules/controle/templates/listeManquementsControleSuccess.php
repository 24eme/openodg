<h2>Cloturer un contrôle</h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<div class="container">
    <form id="formUpdateObservations" action="<?php echo url_for('controle_liste_manquements_controle', array("id" => $controle->_id)) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php foreach ($form as $rtmId => $manquement): ?>
            <?php if ($rtmId == '_revision') {continue;}?>
            <div class="row col-xs-12 checkbox">
                <h4>
                    <?php echo $manquement['manquementCheckbox']->render(['id' => $rtmId]);?>
                    <label for="<?php echo $rtmId; ?>"><?php echo $listeManquements[$rtmId]['libelle_manquement'] ?></label>
                </h4>
            </div>
            <div class="row">
                <div class="col-xs-1"></div>
                <div class="col-xs-9">
                    <p>
                        Parcelles concernées :
                        <?php foreach ($listeManquements[$rtmId]['parcelles_id'] as $parcelle_id):?>
                            <strong><?php echo $parcelle_id; ?> </strong>
                        <?php endforeach; ?>
                    </p>
                    <p>Délais : <?php echo $listeManquements[$rtmId]['delais']; ?></p>
                    <p>Libellé point de controle : <?php echo $listeManquements[$rtmId]['libelle_point_de_controle']; ?></p>
                    <p>Observations : <?php echo $manquement['observations']->render(['class' => 'form-control']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="row col-xs-12">
            <button class="btn btn-secondary pull-left">Ajouter un manquement</button>
            <button type="submit" class="btn btn-success pull-right">Enregistrer</button>
        </div>
    </form>
</div>
