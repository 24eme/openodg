var myApp = angular.module('myApp', ['LocalStorageModule']);

myApp.config(function (localStorageServiceProvider) {
    localStorageServiceProvider
            .setPrefix('AVA')
            .setStorageType('localStorage')
            .setNotify(true, true)
});

myApp.controller('tournee_vtsgnCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', function ($scope, $rootScope, $http, localStorageService) {

        $scope.active = 'recapitulatif';
        $scope.transmission = false;
        $scope.transmission_progress = false;
        $scope.state = true;
        $scope.loaded = false;
        var local_storage_name = $rootScope.url_json;

        var localSave = function () {
            localStorageService.set(local_storage_name, angular.toJson($scope.planification));
        }

        var localDelete = function () {
            localStorageService.remove(local_storage_name);
        }

        var getOperateurById = function (id) {
            for (key in $scope.operateurs) {
                if ($scope.operateurs[key]._id == id) {

                    return $scope.operateurs[key];
                }
            }

            return null;
        }

        var getOperateurKeyById = function (id) {
            for (key in $scope.operateurs) {
                if ($scope.operateurs[key]._id == id) {

                    return key;
                }
            }

            return null;
        }

        var getOperateursToTransmettre = function () {
            var operateursToTransmettre = [];

            for (operateur_key in $scope.operateurs) {
                var operateur = $scope.operateurs[operateur_key];
                if (operateur.transmission_needed) {
                    operateursToTransmettre.push(operateur);
                }
            }

            return operateursToTransmettre;
        }

        var remoteSave = function (operateurs, callBack) {
            if (operateurs.length == 0) {
                callBack(true);
                return;
            }

            $http.post($rootScope.url_json, angular.toJson(operateurs))
                    .success(function (data) {
                        callBack(data);
                    }).error(function (data) {
                callBack(false);
            });
        }

        $scope.testState = function () {
            $http.get($rootScope.url_state).success(function (data) {
                $scope.state = data.authenticated;
            });
        }

      

        var updateOperateurFromLoad = function (operateur) {
            var termine = false;
            var nb_prelevements = 0;

            for (prelevement_key in operateur.prelevements) {
                var prelevement = operateur.prelevements[prelevement_key];
                if (prelevement.preleve && prelevement.hash_produit && prelevement.cuve) {
                    termine = true;
                    nb_prelevements++;
                }

                if (!prelevement.preleve && prelevement.motif_non_prelevement) {
                    termine = true;
                }

                prelevement.produit = {trackby: prelevement.hash_produit + prelevement.vtsgn};
            }
            operateur.termine = termine;
            operateur.nb_prelevements = nb_prelevements;

            if (operateur.motif_non_prelevement && !operateur.nb_prelevements) {
                operateur.termine = true;
                operateur.aucun_prelevement = true;
            }
        }

        $scope.loadOrUpdatePlanification = function () {
            $http.get($rootScope.url_json)
                    .success(function (data) {
                        
                        $scope.loaded = true;                
                        $scope.planification = data;
                        localSave();
                    });
        }

        setInterval(function () {
            $scope.testState();
        }, 200000);

        setInterval(function () {
          //  $scope.transmettre(true);
        }, 30000);

        setInterval(function () {
            $scope.loadOrUpdatePlanification();
        }, 60000);

        if ($scope.reload) {
            localDelete();
        }

        $scope.operateurs = localStorageService.get(local_storage_name);

        if ($scope.operateurs) {
            $scope.loaded = true;
        }

        if (!$scope.operateurs) {
            $scope.operateurs = [];
        }

        $scope.loadOrUpdatePlanification();

        $scope.updateActive = function (key) {
            $scope.active = key;
            $scope.transmission = false;
        }

        $scope.precedent = function () {
            $scope.updateActive('recapitulatif');
        }

        $scope.updateProduit = function (prelevement) {
            prelevement.libelle = prelevement.produit.libelle;
            prelevement.libelle_produit = prelevement.produit.libelle_produit;
            prelevement.hash_produit = prelevement.produit.hash_produit;
            prelevement.vtsgn = prelevement.produit.vtsgn;

            var code_cepage = prelevement.hash_produit.substr(-2);

            if (prelevement.vtsgn) {
                code_cepage += prelevement.vtsgn;
            }
            prelevement.anonymat_prelevement_complet = prelevement.anonymat_prelevement_complet.replace(new RegExp("^[a-zA-Z0-9_]+ "), code_cepage + " ");
            prelevement.show_produit = false;
            prelevement.preleve = 1;
            localSave();
        }

        $scope.terminer = function (operateur) {
            $scope.valide(operateur);

            if (operateur.has_erreurs) {

                return;
            }

            $scope.precedent(operateur);

            localSave();

            operateur.transmission_needed = true;
            $scope.transmettre(true);
        }

        $scope.valide = function (operateur) {
            operateur.termine = false;
            operateur.has_erreurs = false;
            operateur.erreurs = [];
            var nb = 0;
            var nb_prelevements = 0;
            for (prelevement_key in operateur.prelevements) {
                var prelevement = operateur.prelevements[prelevement_key];
                prelevement.erreurs = [];

                if (operateur.aucun_prelevement && operateur.motif_non_prelevement) {
                    prelevement.preleve = 0;
                    prelevement.motif_non_prelevement = null;
                }

                if (prelevement.preleve && !prelevement.cuve) {
                    prelevement.erreurs['cuve'] = true;
                    operateur.has_erreurs = true;
                }
                if (prelevement.preleve && !prelevement.hash_produit) {
                    prelevement.erreurs['hash_produit'] = true;
                    operateur.has_erreurs = true;
                }
                if (!operateur.aucun_prelevement && !prelevement.preleve && prelevement.hash_produit && !prelevement.motif_non_prelevement) {
                    prelevement.erreurs['motif'] = true;
                    operateur.has_erreurs = true;
                }
                if (prelevement.preleve) {
                    nb++;
                    nb_prelevements++;
                }

                if (!prelevement.preleve && prelevement.motif_non_prelevement) {
                    nb++;
                }
            }

            if (!nb && !operateur.aucun_prelevement) {
                operateur.has_erreurs = true;
                operateur.erreurs["aucun_prelevement"] = true;
            }

            if (operateur.aucun_prelevement && !operateur.motif_non_prelevement) {
                operateur.has_erreurs = true;
                operateur.erreurs["motif"] = true;
            }

            operateur.nb_prelevements = nb_prelevements;

            localSave();

            if (operateur.has_erreurs) {

                return;
            }

            if (!operateur.aucun_prelevement && operateur.motif_non_prelevement) {
                operateur.motif_non_prelevement = null;
            }

            for (prelevement_key in operateur.prelevements) {
                var prelevement = operateur.prelevements[prelevement_key];
                if (!prelevement.preleve && prelevement.motif_non_prelevement == "REPORT") {
                    operateur.motif_non_prelevement = "REPORT";
                }
            }

            if (!nb_prelevements && nb && !operateur.motif_non_prelevement) {
                for (prelevement_key in operateur.prelevements) {
                    var prelevement = operateur.prelevements[prelevement_key];
                    if (operateur.motif_non_prelevement && prelevement.motif_non_prelevement != operateur.motif_non_prelevement) {
                        operateur.motif_non_prelevement = "MIXTE";
                    } else {
                        operateur.motif_non_prelevement = prelevement.motif_non_prelevement;
                    }
                }
            }

            if (!nb_prelevements && nb && !operateur.motif_non_prelevement) {
                operateur.motif_non_prelevement = "MIXTE";
            }

            operateur.termine = true;
        }

        $scope.blurOnEnter = function (event) {
            if (event.keyCode != 13) {
                return
            }

            event.target.blur();
        }

        $scope.blur = function (event) {
            localSave();
        }

        $scope.togglePreleve = function (prelevement) {
            if (prelevement.preleve) {
                prelevement.preleve = 0;
            } else {
                prelevement.preleve = 1;
                prelevement.motif_non_prelevement = null;
            }

            $scope.updatePreleve(prelevement);
        }

//        $scope.toggleOperateurAucun = function (operateur) {
//            if (operateur.erreurs) {
//                operateur.erreurs["aucun_prelevement"] = false;
//            }
//            if (operateur.aucun_prelevement) {
//                operateur.aucun_prelevement = 0;
//                operateur.motif_non_prelevement = null;
//            } else {
//                operateur.aucun_prelevement = 1;
//            }
//            localSave();
//        }

        $scope.updateMotif = function (operateur) {
            if (operateur.erreurs) {
                operateur.erreurs["motif"] = false;
            }
            localSave();
        }

        $scope.updateMotifPrelevement = function (operateur) {

            localSave();
        }

        $scope.updatePreleve = function (prelevement) {
            localSave();
        }

        $scope.print = function () {
            window.print();
        }
    }]);
