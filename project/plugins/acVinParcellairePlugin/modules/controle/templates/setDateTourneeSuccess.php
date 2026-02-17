<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href=""><?php echo $controle->declarant->nom ?> (<?php echo $controle->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">Affecter une date de tournée</a></li>
</ol>

<h2 class="mb-4">Affecter une date de tournée au controle</h2>

<div class="well">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<div class="row">
    <div class="col-xs-offset-2 col-xs-8">
        <form method=POST class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-4 control-label" for="date_tournee">Date de la tournée</label>
                <div class="col-sm-4"><input class="form-control" name="date_tournee" id="date_tournee" type="date"/></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="type_tournee">Type de tournée</label>
                <div class="col-sm-4">
                    <select class="form-control" name="type_tournee" id="type_tournee">
                    <option><?php echo ControleClient::CONTROLE_TYPE_SUIVI; ?></option>
                    <option><?php echo ControleClient::CONTROLE_TYPE_HABILITATION; ?></option>
                    </select>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-xs-6 text-left">
                    <a class="btn btn-default" href="<?php echo url_for('controle_index'); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
                </div>
                <div class="col-xs-6 text-right">
                    <button class="btn btn-primary" type="submit">Valider</button>
                </div>
            </div>
        </form>
    </div>
</div>
