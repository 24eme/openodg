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
    
    var local_storage_name = $rootScope.url_json;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.operateurs));
    }

    var remoteSave = function(callBack) {
        $http.post($rootScope.url_json, angular.toJson($scope.operateurs))
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

    $scope.operateurs = localStorageService.get(local_storage_name);

    if(!$scope.operateurs) {
        $http.get($rootScope.url_json)
        .success(function(data){
            $scope.operateurs = data;
        });
    }

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
        prelevement.anonymat_prelevement = code_cepage + prelevement.anonymat_prelevement.substr(2, prelevement.anonymat_prelevement.length);
        prelevement.show_produit = false;
        prelevement.preleve = 1;
        localSave();
    }

    $scope.terminer = function(operateur) {
        $scope.valide(operateur);
        
        if(operateur.erreurs) {

            return;
        }

        $scope.precedent(operateur);

        localSave();
        remoteSave();
    }

    $scope.valide = function(operateur) {
        operateur.termine = false;
        operateur.erreurs = false;
        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            prelevement.erreurs = [];
            if(prelevement.preleve && !prelevement.cuve) {
                prelevement.erreurs['cuve'] = true;
                operateur.erreurs = true;
            }
            if(prelevement.preleve && !prelevement.hash_produit) {
                prelevement.erreurs['hash_produit'] = true;
                operateur.erreurs = true;
            }
        }

        localSave();

        if(operateur.erreurs) {

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
            prelevement.preleve=0;
        } else {
            prelevement.preleve=1;
        }

        updatePreleve(prelevement);
    }

    $scope.updatePreleve = function(prelevement) {
        localSave();    
    }
}]);

myApp.controller('affectationCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', '$filter', function($scope, $rootScope, $http, localStorageService, $filter) {

    $scope.active = 'recapitulatif';
    $scope.transmission = false;
    $scope.transmission_progress = false;
    $scope.state = true;
    $scope.query = null;
    $scope.prelevement = null;
    $scope.affectation = null;
    var local_storage_name = $rootScope.url_json;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.affectation));
    }

    var remoteSave = function(callBack) {
        $http.post($rootScope.url_json, angular.toJson($scope.affectation))
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

    $scope.affectation = localStorageService.get(local_storage_name);
   
    if(!$scope.affectation) {
        $http.get($rootScope.url_json)
        .success(function(data){
            $scope.affectation = data;
        });
    }

    $scope.showAjout = function(commission) {
        $scope.commission = commission;
        $scope.prelevement = null;
        $scope.query = null;
        $scope.active = 'ajout';
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
        $scope.active = 'recapitulatif';
    }

    $scope.remove = function(prelevement) {
        prelevement.commission = null;
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
        if(!$('.select2-input').length) {
            console.log('init');
            $('.select2autocomplete').select2({allowClear: true, placeholder: true, openOnEnter: true});
        }
    }

    $scope.valider = function(prelevement) {
        prelevement.erreurs = false;
        for(key_note in prelevement.notes) {
            var note = prelevement.notes[key_note];
            note.erreurs = false;
            if(!note.note) {
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