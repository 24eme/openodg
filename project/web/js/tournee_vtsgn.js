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

        var getConstatsToTransmettre = function () {
            var constatsToTransmettre = [];

            for (constats_key in $scope.constats) {
                var constat = $scope.constats[constats_key];
                if (constat.transmission_needed) {
                    constatsToTransmettre.push(constat);
                }
            }

            return constatsToTransmettre;
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
                       
                        for(var rdv in data){
                        $scope.constats.push(data[rdv]['constats']);
                        }
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

        $scope.constats = localStorageService.get(local_storage_name);
        
        if ($scope.constats) {
            $scope.loaded = true;
        }

        if (!$scope.constats) {
            $scope.constats = [];
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

        $scope.approuver = function (constat) {
            $scope.valide(constat);

            if (constat.has_erreurs) {

                return;
            }

            $scope.precedent(constat);

            localSave();

            constat.transmission_needed = true;
            $scope.transmettre(true);
        }

$scope.transmettre = function(auto) {
        if($scope.transmission_progress) {

            return;
        }        

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = "success";

        var constats = $scope.constats;
        console.log($scope.constats);

        if(auto) {
            var constats = getConstatsToTransmettre();
        }

        remoteSave(constats, function(data) {
            if(!auto) {
                $scope.transmission = true;
            }
            $scope.transmission_progress = false;

            if(data === true) {
                $scope.transmission_result = "aucune_transmission";
                return;
            }

            if(!data) {
               $scope.transmission_result = "error";
               $scope.testState();
               return;
            }

            if(typeof data !== 'object') {
                $scope.transmission_result = "error";
                $scope.testState();
                return;
            }

            for(id_degustation in data) {
                var revision = data[id_degustation];
                var operateur = getOperateurById(id_degustation);
                operateur.transmission_needed = false;
                if(!revision && $scope.transmission_result) {
                    $scope.transmission_result = false;
                    operateur.transmission_collision = true;
                } else {
                    operateur._rev = revision;
                }
            }
                
            localSave();
        });
    }

        $scope.valide = function (constat) {
            constat.termine = false;
            constat.has_erreurs = false;
            constat.erreurs = [];
            var nb = 0;
            var nb_prelevements = 0;
           

            constat.termine = true;
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
