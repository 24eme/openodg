<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $controle->date_tournee)); ?>">Tournée du <?php echo $controle->date_tournee; ?></a></li>
  <li><a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>"><?php echo $controle->declarant->nom ?> (<?php echo $controle->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">Ajouter un manquement</a></li>
</ol>

<h2 class="mb-4">Ajout un manquement</h2>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<form id="formAddManquement" action="<?php echo url_for('controle_ajout_liste_manquements', array("id" => $controle->_id)) ?>" method="post" class="form-horizontal">
    <div class="form-group">
        <label for="manquementSelect" class="col-sm-3 control-label">Choisir un manquement</label>
        <div class="col-sm-7">
            <select class="form-control" name="manquement" id="manquementSelect">
                <option value=""></option>
                <?php foreach ($listeManquements as $idRtm => $manquement): ?>
                    <option value="<?php echo $idRtm ?>"><?php echo $manquement ?></option>
                <?php endforeach;?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-offset-1 col-sm-5 text-left">
            <a class="btn btn-default" href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
        </div>
        <div class="col-sm-4 text-right">
            <button type="submit" class="btn btn-success">Ajouter</button>
        </div>
    </div>
</form>
