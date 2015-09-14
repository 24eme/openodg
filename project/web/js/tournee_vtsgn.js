var myApp = angular.module('myApp', ['LocalStorageModule']);

myApp.config(function (localStorageServiceProvider) {
    localStorageServiceProvider
            .setPrefix('AVA')
            .setStorageType('localStorage')
            .setNotify(true, true)
});

myApp.controller('tournee_vtsgnCtrl', ['$window', '$scope', '$rootScope', '$http', 'localStorageService', function ($window, $scope, $rootScope, $http, localStorageService) {

        $scope.active = 'recapitulatif';
        $scope.activeRdv = null;
        $scope.transmission = false;
        $scope.transmission_progress = false;
        $scope.state = true;
        $scope.loaded = false;
        var signaturePad = null;

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


        $scope.loadOrUpdatePlanification = function () {
            $http.get($rootScope.url_json)
                    .success(function (data) {

                        $scope.loaded = true;
                        $scope.planification = data;

                        for (var rdv in data) {
                            for (var constatId in data[rdv]['constats']) {
                                var constat = data[rdv]['constats'][constatId];
                                constat._idNode = constatId;
                                $scope.constats.push(constat);
                            }
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
            //$scope.loadOrUpdatePlanification();
        }, 60000);

        if ($scope.reload) {
            localDelete();
        }

        //$scope.constats = localStorageService.get(local_storage_name);

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

        $scope.precedent = function() {
            $scope.updateActive('recapitulatif');
        }

        $scope.mission = function(rdv) {
            $scope.updateActive('mission');
            $scope.activeRdv = rdv;
            $scope.activeConstat = null;
        }

        $scope.showConstat = function(constat) {
            $scope.activeConstat = constat;

            if(constat.type_constat == 'raisin' && constat.statut_raisin == 'REFUSE') {
                $scope.refuserConfirmation(constat);

                return;
            }

            if(constat.type_constat == 'volume' && constat.statut_volume == 'REFUSE') {
                $scope.refuserConfirmation(constat);
                
                return;
            }

            $scope.remplir(constat);
        }

        $scope.remplir = function(constat) {
            $scope.activeConstat = constat;
            $scope.updateActive('saisie');
        }

        $scope.approuverConstatRaisin = function (constat) {
            $scope.valideConstatRaisin(constat);

            if (constat.has_erreurs) {

                return;
            }

            $scope.mission($scope.activeRdv);

            constat.statut_raisin = 'APPROUVE';
            constat.transmission_needed = true;

            localSave();
            $scope.transmettre(true);
        }

        $scope.signature = function (constat, id) {
            $scope.valideConstatVolume(constat);

            if (constat.has_erreurs) {

                return;
            }

            $scope.updateActive('signature');

            var wrapper = document.getElementById(id);
            $(wrapper).removeClass('ng-hide');
            canvas = wrapper.querySelector("canvas");

            canvas.width = canvas.clientWidth;
            signaturePad = new SignaturePad(canvas);

            if($rootScope.signatureImg && !constat.signature) {
                constat.signature = $rootScope.signatureImg;
            }

            if(constat.signature) {
                signaturePad.fromDataURL(constat.signature);
            }
        }

        $scope.approuverConstatVolume = function (constat) {
            constat.signature = null;
            if(!signaturePad.isEmpty()) {
                constat.signature = signaturePad.toDataURL();
            }

            $scope.valideConstatVolumeSignature(constat);

            $rootScope.signatureImg = constat.signature;

            if (constat.has_erreurs) {

                return;
            }

            $scope.mission($scope.activeRdv);

            signaturePad.off();
            signaturePad = null;

            constat.statut_volume = 'APPROUVE';
            constat.transmission_needed = true;

            localSave();
            $scope.transmettre(true);
        }
    
        $scope.refuserConstatRaisin = function (constat) {
            constat.statut_raisin = 'REFUSE';
            $scope.mission($scope.activeRdv);

            constat.transmission_needed = true;

            localSave();
            $scope.transmettre(true);
        }

        $scope.refuserConstatVolume = function (constat) {
            constat.statut_volume = 'REFUSE';
            $scope.mission($scope.activeRdv);
            
            constat.transmission_needed = true;

            localSave();
            $scope.transmettre(true);
        }

        $scope.refuserConfirmation = function (constat) {
            $scope.updateActive('refuser_confirmation');
        }

        $scope.ajoutConstat = function(rdvConstats) {

            var nouveauConstat = {};
            var idNewNode = $rootScope.date.replace("-", "", "g") + '_' + UUID.generate();
            nouveauConstat.type_constat = 'raisin'; 
            nouveauConstat.statut_raisin = 'NONCONSTATE';             
            nouveauConstat.statut_volume = 'NONCONSTATE';   
            nouveauConstat.rendezvous_raisin = rdvConstats.idrdv;
            nouveauConstat.idconstatdoc = rdvConstats.rendezvous.constat;
            nouveauConstat.idconstatnode = idNewNode;
            nouveauConstat.nom_agent_origine = rdvConstats.rendezvous.nom_agent_origine;
            var constatId = rdvConstats.rendezvous.constat + '_' + idNewNode;
            rdvConstats['constats'][constatId] = nouveauConstat;
            $scope.constats.push(nouveauConstat);
            $scope.showConstat(rdvConstats['constats'][constatId]);
        }

        $scope.transmettre = function (auto) {
            if ($scope.transmission_progress) {

                return;
            }

            $scope.transmission = false;
            $scope.transmission_progress = true;
            $scope.transmission_result = "success";

            var constats = $scope.constats;

            if (auto) {
                var constats = getConstatsToTransmettre();
            }

            remoteSave(constats, function (data) {
                if (!auto) {
                    $scope.transmission = true;
                }
                $scope.transmission_progress = false;

                if (data === true) {
                    $scope.transmission_result = "aucune_transmission";
                    return;
                }

                if (!data) {
                    $scope.transmission_result = "error";
                    $scope.testState();
                    return;
                }

                if (typeof data !== 'object') {
                    $scope.transmission_result = "error";
                    $scope.testState();
                    return;
                }

                for (id_degustation in data) {
                    var revision = data[id_degustation];
                    var operateur = getOperateurById(id_degustation);
                    operateur.transmission_needed = false;
                    if (!revision && $scope.transmission_result) {
                        $scope.transmission_result = false;
                        operateur.transmission_collision = true;
                    } else {
                        operateur._rev = revision;
                    }
                }

                localSave();
            });
        }

        $scope.valideConstatRaisin = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];
            
            if(!constat.produit) {
                constat.erreurs['produit'] = true;
                constat.has_erreurs = true;
            }

            if(!constat.nb_botiche) {
                constat.erreurs['nb_botiche'] = true;
                constat.has_erreurs = true;
            }

            if(!constat.contenant) {
                constat.erreurs['contenant'] = true;
                constat.has_erreurs = true;
            }

            if(!constat.degre_potentiel_raisin) {
                constat.erreurs['degre_potentiel_raisin'] = true;
                constat.has_erreurs = true;
            }
        }

        $scope.valideConstatVolume = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];
            
            if(!constat.degre_potentiel_volume) {
                constat.erreurs['degre_potentiel_volume'] = true;
                constat.has_erreurs = true;
            }

            if(!constat.volume_obtenu) {
                constat.erreurs['volume_obtenu'] = true;
                constat.has_erreurs = true;
            }

            /*if(!constat.type_vtsgn) {
                constat.erreurs['type_vtsgn'] = true;
                constat.has_erreurs = true;
            }*/
        }

        $scope.valideConstatVolumeSignature = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];

            if(!constat.signature) {
                constat.erreurs['signature'] = true;
                constat.has_erreurs = true;
            }
        }

        $scope.blurOnEnter = function (event) {
            if (event.keyCode != 13) {
                return
            }

            event.target.blur();
        }

        $scope.updateContenant = function(constat) {
            constat.contenant_libelle = $rootScope.contenants[constat.contenant];
        }

        $scope.updateRaisonRefus = function(constat) {
            constat.raison_refus_libelle = $rootScope.raisons_refus[constat.raison_refus];
        }

        $scope.updateProduit = function(constat) {
            constat.produit_libelle = $rootScope.produits[constat.produit];
        }

        $scope.blur = function (event) {
            localSave();
        }

        $scope.print = function () {
            window.print();
        }
    }]);