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
    $.getJSON("/degustation/tournee/DEGUSTATION-20150311-ALSACE/COMPTE-A008482/2015-03-09.json", 
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

    $scope.terminer = function() {
        $scope.updateActive('recap');
    }
}]);



