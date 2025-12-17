<h2>Affecter une date de tournée au controle de <?php echo $controle->declarant->nom; ?></h2>

<div class="row">
<div class="col-xs-6">
<form method=POST>
    <div class="form-group">
        <label for="date_tournee">Date de la tournée</label>
        <input class="form-control" name="date_tournee" id="date_tournee" type="date"/>
    </div>
    <div class="form-group">
        <label for="type_tournee">Type de tournée</label>
        <select class="form-control" name="type_tournee" id="type_tournee">
            <option><?php echo ControleClient::CONTROLE_TYPE_SUIVI; ?></option>
            <option><?php echo ControleClient::CONTROLE_TYPE_HABILITATION; ?></option>
        </select>
    </div>
    <div class="col-xs-8">
    <button class="btn btn-primary" type="submit">Enregistrer</button>
    </div>
</form>
</div>
</div>
