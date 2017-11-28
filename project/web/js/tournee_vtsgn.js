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
        $scope.produitFilterAppellation = {hash: ''};
        $scope.produitFilterCepage = {hash: ''};
        $scope.constats = [];

        $scope.produitsAppellation = [
            {'hash': 'appellation_ALSACEBLANC', 'libelle': 'AOC Alsace blanc'},
            {'hash': 'appellation_COMMUNALE', 'libelle': 'AOC Alsace Communale'},
            {'hash': 'appellation_LIEUDIT', 'libelle': 'AOC Alsace Lieu-dit'},
            {'hash': 'appellation_GRDCRU', 'libelle': 'AOC Alsace Grand Cru'}
        ];

        $scope.produitsCepage = [
            {'hash': 'cepage_RI', 'libelle': 'Riesling'},
            {'hash': 'cepage_PG', 'libelle': 'Pinot Gris'},
            {'hash': 'cepage_MU', 'libelle': 'Muscat'},
            {'hash': 'cepage_MO', 'libelle': 'Muscat Ottonel'},
            {'hash': 'cepage_GW', 'libelle': 'Gewurztraminer'}
        ];

        var signaturePad = null;

        $scope.produitsAll = [];
        for (produit_hash in $rootScope.produits) {
            $scope.produitsAll.push({hash: produit_hash, libelle: $rootScope.produits[produit_hash]});
        }

        var local_storage_name = $rootScope.url_json;

        var localSave = function () {
            localStorageService.set(local_storage_name, angular.toJson($scope.planification));
        }

        $scope.updateRdv = function (rdv) {
            termine = true;

            rdv.rendezvous.nb_refuses = 0;
            rdv.rendezvous.nb_approuves = 0;
            rdv.rendezvous.nb_non_saisis = 0;
            rdv.rendezvous.nb_assembles = 0;

            for (constat_key in rdv.constats) {
                constat = rdv.constats[constat_key];

                if (constat.type_constat == 'raisin' && constat.statut_raisin == 'REFUSE') {
                    rdv.rendezvous.nb_refuses += 1;
                } else if (constat.type_constat == 'raisin' && constat.statut_raisin == 'APPROUVE') {
                    rdv.rendezvous.nb_approuves += 1;
                } else if (constat.type_constat == 'raisin') {
                    rdv.rendezvous.nb_non_saisis += 1;
                    termine = false;
                }

                if (constat.type_constat == 'volume' && constat.statut_volume == 'REFUSE') {
                    if (constat.raison_refus == 'ASSEMBLE') {
                        rdv.rendezvous.nb_assembles += 1;
                    } else {
                        rdv.rendezvous.nb_refuses += 1;
                    }
                } else if (constat.type_constat == 'volume' && constat.statut_volume == 'APPROUVE') {
                    rdv.rendezvous.nb_approuves += 1;
                } else if (constat.type_constat == 'volume') {
                    rdv.rendezvous.nb_non_saisis += 1;
                    termine = false;
                }
            }

            rdv.rendezvous.termine = termine;
        }

        var localGet = function () {
            $scope.planification = localStorageService.get(local_storage_name);
            for (var rdv in $scope.planification) {
                for (var constatId in $scope.planification[rdv]['constats']) {
                    var constat = $scope.planification[rdv]['constats'][constatId];
                    constat._idNode = constatId;
                    $scope.constats.push(constat);
                }
                $scope.updateRdv($scope.planification[rdv]);
            }


            if ($scope.planification) {
                $scope.loaded = true;
            }

            if (!$scope.planification) {
                $scope.planification = [];
            }

            $scope.loadOrUpdatePlanification();
        }

        var localDelete = function () {
            localStorageService.remove(local_storage_name);
        }

        var getRdvById = function (id) {
            for (key in $scope.planification) {
                if ($scope.planification[key].idrdv == id) {

                    return $scope.planification[key];
                }
            }

            return null;
        }

        var getConstatById = function (id) {
            for (key in $scope.constats) {
                if ($scope.constats[key]._idNode == id) {

                    return $scope.constats[key];
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

        var transmissionNeeded = function () {

            return getConstatsToTransmettre().length > 0;
        }

        var remoteSave = function (constats, callBack) {
            if (constats.length == 0) {
                callBack(true);
                return;
            }
            $http.post($rootScope.url_json, angular.toJson(constats))
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
            if ($scope.transmission_progress) {
                return;
            }

            if (transmissionNeeded()) {
                $scope.transmettre(true);
                return;
            }


            $http.get($rootScope.url_json)
                    .success(function (data) {

                        //$scope.planification = data;

                        for (var rdv in data) {
                            var rdvObj = getRdvById(data[rdv].idrdv);
                            if (!rdvObj) {
                                rdvObj = data[rdv];
                                $scope.planification.push(rdvObj);
                            }
                            for (var constatId in data[rdv]['constats']) {
                                if (!rdvObj['constats'][constatId]) {
                                    var constat = data[rdv]['constats'][constatId];
                                    constat._idNode = constatId;
                                    rdvObj['constats'][constatId] = constat;
                                    $scope.constats.push(constat);
                                } else {
                                    var constat = rdvObj['constats'][constatId];
                                    var newConstat = data[rdv]['constats'][constatId];

                                    if (newConstat.type_constat == 'raisin' && newConstat.statut_raisin != 'NONCONSTATE' && constat.type_constat == 'raisin' && constat.statut_raisin != newConstat.statut_raisin) {
                                        newConstat._idNode = constatId;
                                        rdvObj['constats'][constatId] = newConstat;
                                    }

                                    if (newConstat.type_constat == 'volume' && newConstat.statut_volume != 'NONCONSTATE' && constat.type_constat == 'volume' && constat.statut_volume != newConstat.statut_volume) {
                                        newConstat._idNode = constatId;
                                        rdvObj['constats'][constatId] = newConstat;
                                    }
                                }

                                if (!getConstatById(constatId))  {
                                    var constat = rdvObj['constats'][constatId];
                                    constat._idNode = constatId;
                                    $scope.constats.push(constat);
                                }
                            }
                            rdvObj.heure = data[rdv].heure;
                            rdvObj.rendezvous = data[rdv].rendezvous;
                            $scope.updateRdv(rdvObj);
                        }

                        for(var rdv in $scope.planification) {
                            var rdvObj = $scope.planification[rdv];
                            if(!data[rdvObj.idrdv]) {
                                rdvObj.annule = true;
                            } else {
                                rdvObj.annule = false;
                            }
                        }

                        localSave();
                        $scope.loaded = true;
                    });
        }

        $scope.transmettre = function (auto) {
            if ($scope.transmission_progress) {

                return;
            }

            $scope.transmission = false;
            $scope.transmission_progress = true;
            $scope.transmission_result = "success";

            var constats = $scope.constats;

            var constats = getConstatsToTransmettre();

            remoteSave(constats, function (data) {
                if (!auto) {
                    $scope.transmission = true;
                }


                if (data === true) {
                    $scope.transmission_result = "success";
                    $scope.transmission_progress = false;
                    return;
                }

                if (!data) {
                    $scope.transmission_result = "error";
                    $scope.transmission_progress = false;
                    $scope.testState();
                    return;
                }

                if (typeof data !== 'object') {
                    $scope.transmission_result = "error";
                    $scope.transmission_progress = false;
                    $scope.testState();
                    return;
                }


                for (id_constat in data) {
                    //var revision = data[id_constat];
                    var constat = getConstatById(id_constat);
                    constat.transmission_needed = false;
                    /*if (!revision && $scope.transmission_result) {
                     $scope.transmission_result = false;
                     operateur.transmission_collision = true;
                     } else {
                     operateur._rev = revision;
                     }*/
                }

                localSave();
                $scope.transmission_progress = false;
            });
        }

        setInterval(function () {
            $scope.testState();
        }, 200000);

        setInterval(function () {
            $scope.loadOrUpdatePlanification();
        }, 60000);

        if ($scope.reload) {
            localDelete();
        }

        localGet();

        $scope.updateActive = function (key) {
            $scope.active = key;
            $scope.transmission = false;
        }

        $scope.precedent = function () {
            $scope.updateActive('recapitulatif');
        }

        $scope.mission = function (rdv) {
            $scope.updateActive('mission');
            $scope.activeRdv = rdv;
            $scope.activeConstat = null;
        }

        $scope.showConstat = function (constat) {
            $scope.activeConstat = constat;

            if (constat.type_constat == 'raisin' && constat.statut_raisin == 'REFUSE') {
                $scope.refuserConfirmation(constat);

                return;
            }

            if (constat.type_constat == 'volume' && constat.statut_volume == 'REFUSE') {
                $scope.refuserConfirmation(constat);

                return;
            }

            $scope.remplir(constat);
        }

        $scope.remplir = function (constat) {
            $scope.activeConstat = constat;
            $scope.updateActive('saisie');
        }

        $scope.showChoixProduit = function () {
            $scope.updateActive('choix_produit');
            $scope.resetFilterAppellation();
            $scope.resetFilterCepage();
        }

        $scope.approuverConstatRaisin = function (constat) {
            $scope.valideConstatRaisin(constat);

            if (constat.has_erreurs) {

                return;
            }

            $scope.mission($scope.activeRdv);

            constat.statut_raisin = 'APPROUVE';
            constat.transmission_needed = true;

            $scope.updateRdv($scope.activeRdv);

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

            if ($rootScope.signatureImg && !constat.signature) {
                //constat.signature = $rootScope.signatureImg;
            }

            wrapper.querySelector('.signature-pad-clear').onclick = function() {
                if(confirm("Étes-vous sûr de vouloir effacer la signature ?")) {
                    signaturePad.clear();
                }
            };

            if (constat.signature) {
                signaturePad.fromDataURL(constat.signature);
            }
        }

        $scope.approuverConstatVolume = function (constat) {
            constat.signature = null;
            if (!signaturePad.isEmpty()) {
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

            $scope.updateRdv($scope.activeRdv);

            localSave();
            $scope.transmettre(true);
        }

        $scope.refuserConstatRaisin = function (constat) {
            constat.statut_raisin = 'REFUSE';
            $scope.mission($scope.activeRdv);

            constat.transmission_needed = true;

            $scope.updateRdv($scope.activeRdv);

            localSave();
            $scope.transmettre(true);
        }

        $scope.refuserConstatVolume = function (constat) {
            constat.statut_volume = 'REFUSE';
            $scope.mission($scope.activeRdv);

            constat.transmission_needed = true;

            $scope.updateRdv($scope.activeRdv);

            localSave();
            $scope.transmettre(true);
        }

        $scope.assemblerConstatVolume = function (constat) {
            constat.statut_volume = 'REFUSE';
            constat.raison_refus = 'ASSEMBLE';
            $scope.mission($scope.activeRdv);

            constat.transmission_needed = true;

            $scope.updateRdv($scope.activeRdv);

            localSave();
            $scope.transmettre(true);
        }


        $scope.refuserConfirmation = function (constat) {
            $scope.updateActive('refuser_confirmation');
        }

        $scope.assembleConfirmation = function (constat) {
            $scope.updateActive('assemble_confirmation');
        }

        $scope.ajoutConstat = function (rdvConstats) {

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
            nouveauConstat._idNode = constatId;
            rdvConstats['constats'][constatId] = nouveauConstat;
            $scope.constats.push(nouveauConstat);
            $scope.showConstat(rdvConstats['constats'][constatId]);
            $scope.updateRdv(rdvConstats);
            localSave();
        }

        $scope.valideConstatRaisin = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];

            if (!constat.produit) {
                constat.erreurs['produit'] = true;
                constat.has_erreurs = true;
            }

            console.log(constat.produit);

            if (!constat.denomination_lieu_dit && constat.produit.match(/appellation_LIEUDIT/)) {
                constat.erreurs['denomination_lieu_dit'] = true;
                constat.has_erreurs = true;
            }

            if (!constat.nb_contenant) {
                constat.erreurs['nb_contenant'] = true;
                constat.has_erreurs = true;
            }

            if (!constat.contenant) {
                constat.erreurs['contenant'] = true;
                constat.has_erreurs = true;
            }

            if (!constat.degre_potentiel_raisin) {
                constat.erreurs['degre_potentiel_raisin'] = true;
                constat.has_erreurs = true;
            }
        }

        $scope.valideConstatVolume = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];

            if (!constat.degre_potentiel_volume) {
                constat.erreurs['degre_potentiel_volume'] = true;
                constat.has_erreurs = true;
            }

            if (!constat.volume_obtenu) {
                constat.erreurs['volume_obtenu'] = true;
                constat.has_erreurs = true;
            }

            if (!constat.type_vtsgn) {
                constat.erreurs['type_vtsgn'] = true;
                constat.has_erreurs = true;
            }
        }

        $scope.valideConstatVolumeSignature = function (constat) {
            constat.has_erreurs = false;
            constat.erreurs = [];

            if (!constat.papier && !constat.signature) {
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

        $scope.updateContenant = function (constat) {
            constat.contenant_libelle = $rootScope.contenants[constat.contenant];
        }

        $scope.updateRaisonRefus = function (constat) {
            constat.raison_refus_libelle = $rootScope.raisons_refus[constat.raison_refus];
        }

        $scope.resetFilterAppellation = function () {
            $scope.produitFilterAppellation = {hash: ''}
        }

        $scope.resetFilterCepage = function () {
            $scope.produitFilterCepage = {hash: ''}
        }

        $scope.filterProduitsAppellation = function (produit_hash) {
            $scope.produitFilterAppellation = {hash: produit_hash}
        }

        $scope.filterProduitsCepage = function (produit_hash) {
            $scope.produitFilterCepage = {hash: produit_hash}
        }

        $scope.choixProduit = function (produit) {
            $scope.activeConstat.produit = produit.hash;
            $scope.activeConstat.produit_libelle = produit.libelle;
            $scope.remplir($scope.activeConstat);
            $scope.resetFilterAppellation();
            $scope.resetFilterCepage();
        }

        $scope.blur = function (event) {
            localSave();
        }

        $scope.print = function () {
            window.print();
        }
    }]);
