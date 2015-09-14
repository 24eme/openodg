<div id="saisie_constat_volume_{{ keyConstatNode}}" ng-show="active == 'saisie'" class="col-xs-12 print-margin-bottom">
    <div class="form-horizontal">
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['degre_potentiel_volume'] }" class="alert alert-danger">
                Vous devez saisir le degré potentiel
            </div>
            <div class="col-xs-6">
                <input placeholder="Degré potentiel" id="degre_potentiel_volume{{ keyConstatNode}}" ng-model="constat.degre_potentiel_volume" type="text" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
            </div>
            <div class="col-xs-6 lead">
            <p style="margin: 0;" class="form-control-static">° potentiel</p>
            </div>
        </div>
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['volume_obtenu'] }" class="alert alert-danger">
                Vous devez saisir le volume obtenu
            </div>
            <div class="col-xs-6">
                <input placeholder="Volume obtenu" id="volume_obtenu{{ keyConstatNode}}" ng-model="constat.volume_obtenu" type="text" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
            </div>
            <div class="col-xs-6 lead">
            <p style="margin: 0;" class="form-control-static">hl obtenu</p>
            </div>
        </div>
        <div class="form-group">
            <div ng-class="{ 'hidden': !constat.erreurs['type_vtsgn'] }" class="alert alert-danger">
                Vous devez choisir le type de constat VT ou SGN
            </div>
            <div class="col-xs-12">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default btn-lg btn-default-step">
                        <input type="radio" ng-model="constat.type_vtsgn"> VT
                    </label>
                    <label class="btn btn-default btn-lg btn-default-step">
                        <input type="radio" ng-model="constat.type_vtsgn"> SGN
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
            <div class="col-xs-6">
                <a href="" ng-click="refuserConfirmation(constat)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class="glyphicon glyphicon-remove-circle"></span> Refuser</a>
            </div>
            <div class="col-xs-6">
                <a href="" ng-click="signature(constat, 'signature_' + keyConstatNode)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class=" glyphicon glyphicon-ok-circle"></span> Approuver</a>
            </div>
    </div>
</div>
<div ng-show="active == 'refuser_confirmation'" class="form-horizontal">
    <div class="col-xs-12">
        <div class="col-xs-12">
            <p class="lead text-center"> 
            {{ constat.produit_libelle }}<br />
            {{ constat.type_vtsgn }}<br />
            {{ constat.degre_potentiel_volume }} degré potentiel<br />
            {{ constat.volume_obtenu }} hl<br />
            </p>
        </div>
            <div class="form-group">
                <label for="raison_refus_raisin_{{ keyConstatNode}}" class="col-xs-4 control-label lead">Raison du refus</label>
                <div class="col-xs-8">
                <select id="raison_refus_raisin_{{ keyConstatNode}}" ng-model="constat.raison_refus" class="hidden-print form-control input-lg">
                    <option value="DEGRE_INSUFFISANT">Degré insuffisant</option>
                    <option value="MULTI_CEPAGE">Multi cépage</option>
                    <option value="PRESSURAGE_EN_COURS">Pressurage en cours</option>
                </select>
                </div>
            </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="col-xs-6">
                <a href="" ng-click="remplir(constat)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section">Annuler</a>
            </div>
            <div class="col-xs-6">
                <a href="" ng-click="refuserConstatVolume(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Confirmer</a>
            </div>
        </div>
    </div>
</div>
<div id="signature_{{ keyConstatNode }}" ng-show="active == 'signature'" class="col-xs-12">
    <div class="form-horizontal">
        <label class="text-muted">Signature de l'opérateur :</label>
        <div ng-class="{ 'hidden': !constat.erreurs['signature'] }" class="alert alert-danger">
                L'opérateur doit signer le constat
        </div>
        <div class="signature-pad well">
            <canvas style="width: 100%;" height="318"></canvas>
        </div>
        <div class="form-group">
            <div class="col-xs-12">
                <label class="text-muted">Envoyer le constat à l'adresse email :</label>
                <input placeholder="Adresse email" ng-model="constat.email" type="email" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
            </div>
        </div>
        <div class="row row-margin">
            <div class="col-xs-12">
                <a href="" ng-click="approuverConstatVolume(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section">Terminer</a>
            </div>
        </div>
    </div>
</div>