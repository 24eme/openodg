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

    $scope.precedent = function() {
        $scope.updateActive('recap');
    }

    $scope.updateProduit = function(prelevement) {
        prelevement.libelle = $rootScope.produits[prelevement.hash_produit];
        var code_cepage = prelevement.hash_produit.substr(-2);
        prelevement.anonymat_prelevement = code_cepage + prelevement.anonymat_prelevement.substr(2, prelevement.anonymat_prelevement.length);
        prelevement.show_produit = false;
    }

    $scope.valide = function(key) {

        var operateur = $scope.operateurs[key];

        operateur.termine = false;
        operateur.erreurs = false;
        operateur.prelevements.erreurs = [];

        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];

            if(prelevement.preleve && !prelevement.cuve) {
                operateur.prelevements.erreurs['cuve'] = true;
                operateur.erreurs = true;
            }
        }

        if(operateur.erreurs) {

            return;
        }

        operateur.termine = true;

        $scope.updateActive('recap');
    }
}]);



