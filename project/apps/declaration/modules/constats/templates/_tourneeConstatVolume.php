<div ng-show="!operateur.aucun_prelevement" id="saisie_constat_{{ keyConstatNode}}" class="col-xs-12 print-margin-bottom">
    <div class="visible-print-block" class="row">
        <h3 style="margin-top: 15px" >
            <span class="col-xs-12 control-label text-center">Résumé du constat raisin:</span>             

            <span class="col-xs-12 control-label text-center">
                <div ng-repeat="produit in produits">
                    <span ng-show="produit.hash_produit == constat.produit" > {{ produit.libelle_complet}} de {{ constat.nb_botiche}} botiches à {{ constat.degre_potentiel_raisin}}° potentiel</span>
                </div>
            </span>
        </h3>
    </div>
    <br>
    <br>
    <div class="visible-print-block" class="row">
        <div class="col-xs-12">
            <div class="form-horizontal">

                <div  class="row">                                   
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">
                            <input id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.nb_botiche" type="number" class="form-control input-md hidden-sm hidden-md hidden-lg hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <input id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.nb_botiche" type="number" class="form-control input-lg hidden-xs hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <!--<input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" />--> 
                        </div>
                    </div>
                </div>
                <div  class="row">                                   
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">
                            <input id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_volume" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <input id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_volume" type="text" class="form-control input-lg hidden-xs hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <!--<input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" />--> 
                        </div>
                    </div>
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-default btn-default-step  active">
                                    <input type="radio" name="type_vtsgn" id="VT" autocomplete="off" checked> VT
                                </label>
                                <label class="btn btn-default btn-default-step">
                                    <input type="radio" name="type_vtsgn" id="SGN" autocomplete="off"> SGN
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>