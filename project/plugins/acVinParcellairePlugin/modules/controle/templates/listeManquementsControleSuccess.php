<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee)); ?>">Tournée du <?php echo Date::francizeDate($controle->date_tournee); ?></a></li>
  <li><a href=""><?php echo $controle->declarant->nom ?> (<?php echo $controle->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">Visualisation des manquements</a></li>
</ol>

<form id="formGenerateMouvements" action="<?php echo url_for('controle_update_manquements', array("id" => $controle->_id)) ?>" method="post" class="pull-right">
    <button type="submit" class="btn btn-default mt-3"><span class="glyphicon glyphicon-repeat"></span> Regénérer les manquements</button>
</form>

<h2 class="mb-4">Manquement du contrôle du <?php echo Date::francizeDate($controle->date_tournee); ?></h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<form id="formListeManquements" action="<?php echo url_for('controle_liste_manquements_controle', array("id" => $controle->_id)) ?>" method="post">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($form as $rtmId => $manquement): ?>
        <?php if ($rtmId == '_revision') {continue;}?>
        <div class="mb-4">
            <div class="checkbox">
                <h4>
                    <?php echo $manquement['manquement_checkbox']->render(['id' => $rtmId]);?>
                    <label for="<?php echo $rtmId; ?>"><?php echo $listeManquements[$rtmId]['libelle_manquement'] ?></label>
                </h4>
            </div>
            <div class="pl-4">
                <p>
                    Parcelles concernées :
                    <?php foreach ($listeManquements[$rtmId]['parcelles_id'] as $parcelle_id):?>
                        <strong><?php echo $parcelle_id; ?> </strong>
                    <?php endforeach; ?>
                </p>
                <p>Délais : <?php echo $listeManquements[$rtmId]['delais']; ?></p>
                <p>Point de controle : <?php echo $listeManquements[$rtmId]['libelle_point_de_controle']; ?></p>
                <p>Observations : <?php echo $manquement['observations']->render(['class' => 'form-control']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</form>
<div class="row">
    <div class="col-xs-4"><a class="btn btn-default" href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee)); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
    <div class="col-xs-4 text-center">
    <a class="btn btn-default" href="<?php echo url_for('controle_ajout_liste_manquements', array("id" => $controle->_id))?>"><span class="glyphicon glyphicon-plus"></span> Ajouter un manquement</a>
    </div>
    <div class="col-xs-4"><button type="submit" form="formListeManquements" class="btn btn-primary pull-right">Enregistrer</button></div>
</div>
