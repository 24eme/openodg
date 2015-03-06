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

myApp.controller('tourneeCtrl', ['$scope', function($scope) {
    $scope.prelevements = [];
    $scope.active = 'recap';
    $scope.erreurs = [];

    $.getJSON("/declaration_dev.php/degustation/tournee/DEGUSTATION-20150311-ALSACE/COMPTE-A008482/2015-03-09.json", 
        function(data) {
            $scope.operateurs = data;
            /*for(key in $scope.operateurs) {
                var operateur = $scope.operateurs[key];
                operateur.prelevements.push({});
                operateur.prelevements.push({});
                operateur.prelevements.push({});
            }*/
            $scope.$apply();
        }
    );

    $scope.updateActive = function(key) {
        $scope.active = key;
    }

    $scope.precedent = function() {
        $scope.updateActive('recap');
    }

    $scope.valide = function(key) {

        var operateur = $scope.operateurs[key];

        $scope.erreurs[key] = [];

        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];

            if(prelevement.preleve && !prelevement.cuve) {
                if(!$scope.erreurs[key][prelevement_key]) {
                    $scope.erreurs[key][prelevement_key] = [];
                }
                $scope.erreurs[key][prelevement_key]['cuve'] = 1;
            }
        }

        if($scope.erreurs[key].length) {
            return;
        }

        operateur.termine = true;

        $scope.updateActive('recap');
    }
}]);



