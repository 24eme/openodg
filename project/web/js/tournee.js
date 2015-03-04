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
            console.log('test');
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
    $.getJSON("http://declaration.dev.ava-aoc.fr/declaration_dev.php/degustation/tournee/DEGUSTATION-20150218-ALSACE/COMPTE-A000545/2015-02-11.json", function( data ) {
                $scope.prelevements = data;
                $scope.$apply();
        }
    );
}]);



