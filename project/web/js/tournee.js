var myApp = angular.module('myApp',['LocalStorageModule']);

myApp.config(function (localStorageServiceProvider) {
  localStorageServiceProvider
    .setPrefix('AVA')
    .setStorageType('localStorage')
    .setNotify(true, true)
});

myApp.controller('tourneeCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', function($scope, $rootScope, $http, localStorageService) {

    $scope.active = 'recapitulatif';
    $scope.transmission = false;
    $scope.transmission_progress = false;
    $scope.state = true;
    $scope.loaded = false;
    
    var local_storage_name = $rootScope.url_json;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.operateurs));
    }

    var localDelete = function() {
        localStorageService.remove(local_storage_name);
    }

    var getOperateurById = function(id) {
        for(key in $scope.operateurs) {
            if($scope.operateurs[key]._id == id) {

                return $scope.operateurs[key];
            }
        }

        return null;
    }

    var getOperateurKeyById = function(id) {
        for(key in $scope.operateurs) {
            if($scope.operateurs[key]._id == id) {

                return key;
            }
        }

        return null;
    }

    var getOperateursToTransmettre = function() {
        var operateursToTransmettre = [];

        for(operateur_key in $scope.operateurs) {
            var operateur = $scope.operateurs[operateur_key];
            if(operateur.transmission_needed) {
               operateursToTransmettre.push(operateur); 
            }
        }

        return operateursToTransmettre;
    }

    var remoteSave = function(operateurs, callBack) {
        if(operateurs.length == 0) {
            callBack(true);
            return;
        }

        $http.post($rootScope.url_json, angular.toJson(operateurs))
        .success(function(data){
            callBack(data);
        }).error(function(data) {
            callBack(false);
        });
    }

    $scope.testState = function() {
        $http.get($rootScope.url_state).success(function(data){
            $scope.state = data.authenticated;
        });
    }

    $scope.transmettre = function(auto) {
        if($scope.transmission_progress) {

            return;
        }

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = true;

        remoteSave(getOperateursToTransmettre(), function(data) {
            if(!auto) {
                $scope.transmission = true;
            }
            $scope.transmission_progress = false;

            if(!data) {
               $scope.transmission_result = false;
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

    var updateOperateurFromLoad = function(operateur) {
        var termine = false;
        if(operateur.motif_non_prelevement) {
            termine = true;
            operateur.aucun_prelevement = true;
        }
        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            if(prelevement.preleve && prelevement.hash_produit && prelevement.cuve) {
                termine = true;
            }
        }
        operateur.termine = termine;
    }

    $scope.loadOrUpdateOperateurs = function() {
        $http.get($rootScope.url_json)
        .success(function(data){
            for(key_data in data) {
                var operateurRemote = data[key_data];
                var operateur = getOperateurById(operateurRemote._id);
                if(operateur && operateurRemote._rev == operateur._rev) {

                } else if(operateur && operateur.transmission_needed) {
                
                } else if(operateur && operateur.transmission_collision) {

                } else if(operateur && operateurRemote._rev != operateur._rev) {
                    $scope.operateurs[getOperateurKeyById(operateurRemote._id)] = operateurRemote;
                    updateOperateurFromLoad(operateurRemote);
                } else {
                    $scope.operateurs.push(operateurRemote);
                    updateOperateurFromLoad(operateurRemote);
                }
            }
            $scope.loaded = true;
            localSave();
        });
    }

    setInterval(function() {
        $scope.testState();
    }, 200000);

    setInterval(function() {
        $scope.transmettre(true);
    }, 30000);

    setInterval(function() {
        $scope.loadOrUpdateOperateurs();
    }, 60000);

    if($scope.reload) {
        localDelete();
    }

    $scope.operateurs = localStorageService.get(local_storage_name);

    if($scope.operateurs) {
        $scope.loaded = true;
    }

    if(!$scope.operateurs) {
        $scope.operateurs = [];
    }

    $scope.loadOrUpdateOperateurs();

    $scope.updateActive = function(key) {
        $scope.active = key;
        $scope.transmission = false;
    }

    $scope.precedent = function() {
        $scope.updateActive('recapitulatif');
    }

    $scope.updateProduit = function(prelevement) {
        prelevement.libelle = $rootScope.produits[prelevement.hash_produit];
        var code_cepage = prelevement.hash_produit.substr(-2);
        prelevement.anonymat_prelevement_complet = code_cepage + prelevement.anonymat_prelevement_complet.substr(2, prelevement.anonymat_prelevement_complet.length);
        prelevement.show_produit = false;
        prelevement.preleve = 1;
        localSave();
    }

    $scope.terminer = function(operateur) {
        $scope.valide(operateur);
        
        if(operateur.has_erreurs) {

            return;
        }

        $scope.precedent(operateur);

        localSave();
        operateur.transmission_needed = true;
        $scope.transmettre(true);
    }

    $scope.valide = function(operateur) {
        operateur.termine = false;
        operateur.has_erreurs = false;
        operateur.erreurs = [];
        var nb = 0;
        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            prelevement.erreurs = [];
            if(prelevement.preleve && !prelevement.cuve) {
                prelevement.erreurs['cuve'] = true;
                operateur.has_erreurs = true;
            }
            if(prelevement.preleve && !prelevement.hash_produit) {
                prelevement.erreurs['hash_produit'] = true;
                operateur.has_erreurs = true;
            }
            if(prelevement.preleve) {
                nb++;
            }
        }

        if(!nb && !operateur.aucun_prelevement) {
            operateur.has_erreurs = true;
            operateur.erreurs["aucun_prelevement"] = true;
        }

        if(operateur.aucun_prelevement && !operateur.motif_non_prelevement) {
            operateur.has_erreurs = true;
            operateur.erreurs["motif"] = true;
        }

        localSave();

        if(operateur.has_erreurs) {

            return;
        }

        operateur.termine = true;
    }

    $scope.blurOnEnter = function(event) {
        if (event.keyCode != 13) {
            return
        }

        event.target.blur();    
    }

    $scope.blur = function(event) {
        localSave();
    }

    $scope.togglePreleve = function(prelevement) {
        if(prelevement.preleve) {
            prelevement.preleve = 0;
        } else {
            prelevement.preleve = 1;
        }

        $scope.updatePreleve(prelevement);
    }

    $scope.toggleAucunPrelevement = function(operateur) {
        if(operateur.erreurs) {
            operateur.erreurs["aucun_prelevement"] = false;
        }
        if(operateur.aucun_prelevement) {
            operateur.aucun_prelevement = 0;
            for(prelevement_key in operateur.prelevements) {
                if(operateur.prelevements[prelevement_key].cuve) {
                    operateur.prelevements[prelevement_key].preleve = 1;
                }
            }
            operateur.motif_non_prelevement = null;
        } else {
            operateur.aucun_prelevement = 1;
            for(prelevement_key in operateur.prelevements) {
                operateur.prelevements[prelevement_key].preleve = 0;
            }
        }
        localSave();    
    }

    $scope.updateMotif = function(operateur) {
        if(operateur.erreurs) {
            operateur.erreurs["motif"] = false;
        }
        localSave();
    }

    $scope.updatePreleve = function(prelevement) {
        localSave();
    }

    $scope.print = function() {
        window.print();
    }
}]);

myApp.controller('affectationCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', '$filter', function($scope, $rootScope, $http, localStorageService, $filter) {

    $scope.active = 'recapitulatif';
    $scope.transmission = false;
    $scope.transmission_progress = false;
    $scope.state = true;
    $scope.query = null;
    $scope.prelevement = null;
    $scope.prelevements = [];
    $scope.anonymat_degustation = 1;
    $scope.loaded = false;

    $scope.commissions = [];
    for (var i = 1; i <= $scope.nombre_commissions; i++) {
        $scope.commissions.push(i);
    };

    var local_storage_name = $rootScope.url_json;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.degustations));
    }

    var localDelete = function() {
        localStorageService.remove(local_storage_name);
    }

    var getDegustationsToTransmettre = function() {
        var degustationsToTransmettre = [];

        for(degustation_key in $scope.degustations) {
            var degustation = $scope.degustations[degustation_key];
            if(degustation.transmission_needed) {
               degustationsToTransmettre.push(degustation); 
            }
        }

        return degustationsToTransmettre;
    }

    var updatePrelevements = function() {
        $scope.prelevements = [];
        for(degustation_key in $scope.degustations) {
            var degustation = $scope.degustations[degustation_key];
            for(prelevement_key in degustation.prelevements) {
                var prelevement = degustation.prelevements[prelevement_key];
                prelevement.degustation_id = degustation._id;
                $scope.prelevements.push($scope.degustations[degustation_key].prelevements[prelevement_key]);
            }
        } 
        calculAnonymatDegustation();
    }

    var calculAnonymatDegustation = function() {
        $scope.anonymat_degustation = 1;
        for(prelevement_key in $scope.prelevements) {
            var prelevement = $scope.prelevements[prelevement_key];
            if($scope.anonymat_degustation < (prelevement.anonymat_degustation + 1)) {
                $scope.anonymat_degustation = prelevement.anonymat_degustation + 1;
            }
        }
    }

    $scope.loadOrUpdateDegustations = function() {
        $http.get($rootScope.url_json)
        .success(function(data){
            var modified = false;
            for(key_data in data) {
                var degustationRemote = data[key_data];
                var degustation = $scope.degustations[degustationRemote._id];
                if(degustation && degustationRemote._rev == degustation._rev) {

                } else if(degustation && degustation.transmission_needed) {
                
                } else if(degustation && degustation.transmission_collision) {

                } else if(degustation && degustationRemote._rev != degustation._rev) {
                    $scope.degustations[degustationRemote._id] = degustationRemote;
                    modified = true;
                } else {
                    $scope.degustations[degustationRemote._id] = degustationRemote;
                    modified = true;
                }
            }
            $scope.loaded = true;
            localSave();
            if(modified) {
                updatePrelevements();
            }
        });
    }

    var remoteSave = function(degustations, callBack) {
        if(degustations.length == 0) {
            callBack(true);
            return;
        }

        $http.post($rootScope.url_json, angular.toJson(degustations))
        .success(function(data){
            callBack(data);
        }).error(function(data) {
            callBack(false);
        });
    }

    $scope.testState = function() {
        $http.get($rootScope.url_state).success(function(data){
            $scope.state = data.authenticated;
        });
    }

    $scope.transmettre = function(auto) {
        if($scope.transmission_progress) {

            return;
        }

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = true;
        remoteSave(getDegustationsToTransmettre(), function(data) {
            if(!auto) {
                $scope.transmission = true;
            }
            $scope.transmission_progress = false;

            if(!data) {
               $scope.transmission_result = false;
               return;
            }

            for(id_degustation in data) {
                var revision = data[id_degustation];
                var degustation = $scope.degustations[id_degustation];
                degustation.transmission_needed = false;
                if(!revision && $scope.transmission_result) {
                    $scope.transmission_result = false;
                    degustation.transmission_collision = true;
                } else {
                    degustation._rev = revision;
                }
            }
            
            localSave();
        });
    }

    var intervalState = setInterval(function() {
        $scope.testState();
    }, 200000);

    setInterval(function() {
        $scope.transmettre(true);
    }, 30000);

    setInterval(function() {
        $scope.loadOrUpdateDegustations();
    }, 60000);

    if($scope.reload) {
        localDelete();
    }

    $scope.degustations = localStorageService.get(local_storage_name);

    if($scope.degustations) {
        updatePrelevements();
        $scope.loaded = true;
    } else {
        $scope.degustations = {};    
    }

    $scope.loadOrUpdateDegustations();

    $scope.showAjout = function(commission) {
        $scope.commission = commission;
        $scope.prelevement = null;
        $scope.query = null;
        $scope.active = 'ajout';
        $scope.transmission = false;
    }

    $scope.showAjoutValidation = function(prelevement) {
        $scope.prelevement = prelevement;
        $scope.active = 'ajout_validation';
    }

    $scope.ajouter = function(prelevement) {
        $scope.error_ajout = false;

        if(!prelevement) {
            $scope.error_ajout = true;
            return;
        }
        
        $scope.showAjoutValidation(prelevement);
    }

    $scope.terminer = function() {
        $scope.precedent();
    }

    $scope.precedent = function() {
        $scope.active = 'recapitulatif';
    }


    $scope.remove = function(prelevement) {
        prelevement.commission = null;
        prelevement.anonymat_degustation = null;
        calculAnonymatDegustation();
        $scope.degustations[prelevement.degustation_id].transmission_needed = true;
        $scope.transmettre(true);
        localSave();
    }

    $scope.blurOnEnter = function(event) {
        if (event.keyCode != 13) {
            return
        }

        event.target.blur();    
    }

    $scope.validation = function(prelevement, commission) {
        prelevement.commission = commission;
        prelevement.anonymat_degustation = $scope.anonymat_degustation;
        $scope.degustations[prelevement.degustation_id].transmission_needed = true;
        $scope.anonymat_degustation++;
        $scope.transmettre(true);
        localSave();
        $scope.showAjout(commission);
    }

}]);

myApp.controller('degustationCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', '$filter', function($scope, $rootScope, $http, localStorageService, $filter) {

    $scope.active = 'recapitulatif';
    $scope.transmission = false;
    $scope.transmission_progress = false;
    $scope.state = true;
    
    var local_storage_name = $rootScope.url_json;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.degustation));
    }

    var remoteSave = function(callBack) {
        $http.post($rootScope.url_json, angular.toJson($scope.degustation))
        .success(function(data){
            callBack(data.success);
        }).error(function(data) {
            callBack(false);
        });
    }

    $scope.testState = function() {
        $http.get($rootScope.url_state).success(function(data){
            $scope.state = data.authenticated;
        });
    }

    $scope.transmettre = function() {
        $scope.transmission = false;
        $scope.transmission_progress = true;
        remoteSave(function(success) {
            $scope.transmission = true;
            $scope.transmission_result = success;
            $scope.transmission_progress = false;
        });
        $scope.testState();
    }

    var intervalState = setInterval(function() {
        $scope.testState();
    }, 200000);

    $scope.degustation = localStorageService.get(local_storage_name);
   
    if(!$scope.degustation) {
        $http.get($rootScope.url_json)
        .success(function(data){
            $scope.degustation = data;
        });
    }

    $scope.precedent = function() {
        $scope.showRecap();
    }

    $scope.showRecap = function() {
        $scope.active = 'recapitulatif';
    }

    $scope.showCepage = function(prelevement) {
        $scope.active = 'cepage_' + prelevement.anonymat_degustation;
        $scope.transmission = false;
        if(!$('.select2-input').length) {
            $('.select2autocomplete').select2({allowClear: true, placeholder: true, openOnEnter: true});
        }
    }

    $scope.valider = function(prelevement) {
        prelevement.erreurs = false;
        for(key_note in prelevement.notes) {
            var note = prelevement.notes[key_note];
            note.erreurs = false;
            if(note.note === null) {
                note.erreurs = true;
                prelevement.erreurs = true;
            }
        }

        if(prelevement.erreurs) {
            localSave();
            return;
        }

        prelevement.termine = true;
        localSave();
        $scope.showRecap();
    }

}]);