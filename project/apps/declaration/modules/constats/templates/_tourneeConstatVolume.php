<div id="saisie_constat_volume_{{ keyConstatNode}}" ng-show="active == 'saisie'" class="col-xs-12 print-margin-bottom">
    <div class="form-horizontal">
        <div class="col-xs-12">
            <div class="form-group">
                <div ng-class="{ 'hidden': !constat.erreurs['nb_botiche'] }" class="alert alert-danger">
                    Vous devez saisir une quantité 
                </div>
                <div ng-class="{ 'hidden': !constat.erreurs['contenant'] }" class="alert alert-danger">
                    Vous devez saisir un type de contenant
                </div>
                <div class="col-xs-6">
                    <input placeholder="Nombre" id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.nb_botiche" type="number" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                </div>
                <div class="col-xs-6">
                    <select class="form-control input-lg" ng-change="updateContenant(constat)" ng-model="constat.contenant" ng-options="contenant_key as contenant_libelle for (contenant_key, contenant_libelle) in contenants"></select>
                </div>
            </div>
            <div class="form-group">
                <div ng-class="{ 'hidden': !constat.erreurs['degre_potentiel_raisin'] }" class="alert alert-danger">
                    Vous devez saisir le degré potentiel
                </div>
                <div class="col-xs-6">
                    <input placeholder="Degré potentiel" id="degre_potentiel_volume{{ keyConstatNode}}" ng-model="constat.degre_potentiel_volume" type="text" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                </div>
                <div class="col-xs-6 lead">
                <p style="margin: 0;" class="form-control-static">Degré potentiel</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="col-xs-6">
                <a href="" ng-click="approuver(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class=" glyphicon glyphicon-ok-circle"></span> Approuver</a>
            </div>
            <div class="col-xs-6">
                <a href="" ng-click="refuserConfirmation(constat)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class="glyphicon glyphicon-remove-circle"></span> Refuser</a>
            </div>
        </div>
    </div>
</div>