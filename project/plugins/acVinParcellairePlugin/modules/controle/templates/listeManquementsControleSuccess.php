<h2>Cloturer un contrôle</h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<div class="container">
    <form id="formListeManquements" action="<?php echo url_for('controle_liste_manquements_controle', array("id" => $controle->_id)) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php foreach ($form as $rtmId => $manquement): ?>
            <?php if ($rtmId == '_revision') {continue;}?>
            <div class="row col-xs-12 checkbox">
                <h4>
                    <?php echo $manquement['manquement_checkbox']->render(['id' => $rtmId]);?>
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
    </form>
        <div class="" style="padding-right: 30px; align-items: center;">
            <button class="btn btn-secondary pull-left"><a href="<?php echo url_for('controle_ajout_liste_manquements', array("id" => $controle->_id))?>">Ajouter un manquement</a></button>
            <form id="formGenerateMouvements" action="<?php echo url_for('controle_update_manquements', array("id" => $controle->_id)) ?>" method="post">
                <button type="submit" class="btn btn-success">Générer les manquements</button>
            </form>
            <button type="submit" form="formListeManquements" class="btn btn-success pull-right">Enregistrer</button>
        </div>
</div>
