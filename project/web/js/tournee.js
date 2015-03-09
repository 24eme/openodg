/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function($)
{
    var _doc = $(document);

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function()
    {
        /*$('a.link-to-section').on('click', function() {
            $($(this).attr('href')).removeClass('hidden');
            $(this).closest('section').addClass('hidden');
            $(document).scrollTo($(this).attr('href'));

            return false;
        });*/

    });

})(jQuery);
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



