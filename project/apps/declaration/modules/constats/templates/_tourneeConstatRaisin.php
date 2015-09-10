<div ng-show="!operateur.aucun_prelevement" id="saisie_constat_{{ keyConstatNode}}" class="col-xs-12 print-margin-bottom">
    <div class="page-header form-inline print-page-header" style="margin-top: 5px">
        <h3 style="margin-top: 15px" ng-class="{ 'text-danger': constat.erreurs['hash_produit'] }">
            <span ng-show="!constat.show_produit && constat.hash_produit" class="lead" ng-click="togglePreleve(constat)">{{ prelevement.libelle}}<small ng-show="constat.libelle_produit" class="text-muted-alt"> ({{ constat.libelle_produit}})</small></span>
            <label for="" class="col-xs-5 control-label">Choix du produit:</label>  
            <select style="display: inline-block; width: auto;" class="hidden-print form-control" ng-model="constat.produit" ng-options="produit.libelle_complet for produit in produits"></select>
        </h3>
    </div>
    <div class="visible-print-block" class="row">
        <div class="col-xs-12">
            <div class="form-horizontal">
                <!--                                <div ng-class="{ 'hidden': !prelevement.erreurs['hash_produit'] }" class="alert alert-danger">
                                                    Vous devez séléctionner un cépage
                                                </div>
                                                <div ng-class="{ 'hidden': !prelevement.erreurs['cuve'] }" class="alert alert-danger">
                                                    Vous devez saisir le(s) numéro(s) de cuve(s)
                                                </div>
                -->
                <div  class="row">                                   
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">
                            <input id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.volume_obtenu" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <input id="nb_botiche_{{ keyConstatNode}}" ng-model="constat.volume_obtenu" type="texte" class="form-control input-lg hidden-xs hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <!--<input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" />--> 
                        </div>
                    </div>
                  
                </div>
                <div  class="row">                                   
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">
                            <input id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_raisin" type="text" class="form-control input-md hidden-sm hidden-md hidden-lg hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <input id="degre_potentiel_raisin_{{ keyConstatNode}}" ng-model="constat.degre_potentiel_raisin" type="text" class="form-control input-lg hidden-xs hidden-print" ng-keydown="blurOnEnter($event)" ng-blur="blur()" />
                            <!--<input ng-model="prelevement.volume_revendique" type="text" class="form-control input-lg ng-hide visible-print-inline" />--> 
                        </div>
                    </div>
                    <div class="form-group col-xs-12 col-sm-6 col-md-4 lead">
                        <div class="col-xs-7">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>