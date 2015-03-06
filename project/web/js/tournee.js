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
        $('a.link-to-section').on('click', function() {
            $($(this).attr('href')).removeClass('hidden');
            $(this).closest('section').addClass('hidden');
            $(document).scrollTo($(this).attr('href'));

            return false;
        });


    });

})(jQuery);
var myApp = angular.module('myApp',[]);

myApp.controller('tourneeCtrl', ['$scope', '$rootScope', function($scope, $rootScope) {
    $scope.prelevements = [];
    $scope.active = 'recap';
    $scope.erreurs = [];

    $.getJSON($rootScope.url_json, 
        function(data) {
            $scope.operateurs = data;
            $scope.$apply();
        }
    );

    $scope.updateActive = function(key) {
        $scope.active = key;
    }

    $scope.precedent = function(operateur) {
        $scope.updateActive('recap');
    }

    $scope.updateProduit = function(prelevement) {
        prelevement.libelle = $rootScope.produits[prelevement.hash_produit];
        var code_cepage = prelevement.hash_produit.substr(-2);
        prelevement.anonymat_prelevement = code_cepage + prelevement.anonymat_prelevement.substr(2, prelevement.anonymat_prelevement.length);
        prelevement.show_produit = false;
        prelevement.preleve = 1;
    }

    $scope.terminer = function(operateur) {
        $scope.valide(operateur);
        
        if(operateur.erreurs) {

            return;
        }

        $scope.precedent(operateur);
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

        if(operateur.erreurs) {

            return;
        }

        operateur.termine = true;
    }
}]);



