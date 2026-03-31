<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="<?php echo url_for('controle_operateur', $controle->getEtablissementObject()); ?>"><?php echo $controle->declarant->nom ?> (<?php echo $controle->identifiant ?> - <?php echo $controle->declarant->cvi ?>)</a></li>
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
                <div class="col-sm-4"><input class="form-control" name="date_tournee" id="date_tournee" type="date" value="<?php echo $controle->date_tournee; ?>" required /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="type_tournee">Type de tournée</label>
                <div class="col-sm-4">
                    <select class="form-control" name="type_tournee" id="type_tournee">
                    <?php foreach([ControleClient::CONTROLE_TYPE_CONDITION, ControleClient::CONTROLE_TYPE_SUIVI, ControleClient::CONTROLE_TYPE_DOCUMENTAIRE, ControleClient::CONTROLE_TYPE_HABILITATION] as $type): ?>
                        <option<?php if ($type == $controle->type_tournee): ?> SELECTED <?php endif; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <a title='Les agents sont des contacts configurés avec le tag "agent_controle".' data-placement="bottom" data-toggle="tooltip" class="btn-tooltip btn btn-md" style=""><span class="glyphicon glyphicon-question-sign"></span></a>
                <label class="col-sm-4 control-label" for="agent_identifiant">Agent</label>
                <div class="col-sm-4">
                    <select class="form-control" name="agent_identifiant" id="agent_identifiant">
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent->identifiant ?>" <?php if ($agent->identifiant == $controle->agent_identifiant) echo " SELECTED "; ?>><?php echo $agent->getNomAAfficher() ?></option>
                        <?php endforeach; ?>
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
