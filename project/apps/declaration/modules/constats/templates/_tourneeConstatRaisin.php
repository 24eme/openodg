<div id="saisie_constat_{{ keyConstatNode}}" class="col-xs-12 print-margin-bottom">
    <div class="form-horizontal">
        <div class="col-xs-12">
            <div class="form-group">
                <div class="col-xs-12">
                    <select class="form-control input-lg" ng-model="constat.produit" ng-options="produit.libelle_complet for produit in produits"></select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-6">
                    <input placeholder="Nombre" id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.nb_botiche" type="number" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                </div>
                <div class="col-xs-6">
                    <select class="hidden-print form-control input-lg" ng-model="constat.type_botiche" ng-options="type_botiche.nom for type_botiche in types_botiche"></select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-6">
                    <input placeholder="Degré potentiel" id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_raisin" type="text" class="form-control input-lg" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                    <!--<input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" />--> 
                </div>
                <div class="col-xs-6 lead">
                <p style="margin: 0;" class="form-control-static">Degré potentiel</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="col-xs-4">
                <a href="" ng-click="approuver(constat)" class="btn btn-default btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class=" glyphicon glyphicon-ok-circle"></span> Approuver</a>
            </div>
            <div class="col-xs-4">
                <a href="" ng-click="refuser(operateur)" class="btn btn-danger btn-lg col-xs-6 btn-block btn-upper link-to-section"><span class="glyphicon glyphicon-remove-circle"></span> Refuser</a>
            </div>
            <div class="col-xs-4">
                <a href="" ng-click="report(operateur)" class="btn btn-warning btn-lg col-xs-6 btn-block btn-upper link-to-section">Reporter</a>
            </div>
        </div>
    </div>
    <div class="form-horizontal">


    </div>
</div>