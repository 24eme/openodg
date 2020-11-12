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
    var signaturePad = null;

    var localSave = function() {
        localStorageService.set(local_storage_name, angular.toJson($scope.operateurs));
        localStorageService.set(local_storage_name + ".date", new Date());
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

        if(!auto) {
            if(!confirm("Êtes vous sur de vouloir transmettre les données ?")) {

                return;
            }
        }

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = "success";

        var operateurs = $scope.operateurs;

        if(auto) {
            var operateurs = getOperateursToTransmettre();
        }

        remoteSave(operateurs, function(data) {
            if(!auto) {
                $scope.transmission = true;
            }
            $scope.transmission_progress = false;

            if(data === true) {
                $scope.transmission_result = "aucune_transmission";
                return;
            }

            if(!data) {
               $scope.transmission_result = "error";
               $scope.testState();
               return;
            }

            if(typeof data !== 'object') {
                $scope.transmission_result = "error";
                $scope.testState();
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
        var nb_prelevements = 0;

        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            if(prelevement.preleve && prelevement.hash_produit && prelevement.cuve) {
                termine = true;
                nb_prelevements++;
            }

            if(!prelevement.preleve && prelevement.motif_non_prelevement) {
                termine = true;
            }

            prelevement.produit = { trackby: prelevement.hash_produit + prelevement.vtsgn };
        }
        operateur.termine = termine;
        operateur.nb_prelevements = nb_prelevements;

        if(operateur.motif_non_prelevement && !operateur.nb_prelevements) {
            operateur.termine = true;
            operateur.aucun_prelevement = true;
        }
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
        var operateur = getOperateurById(key);
        if(operateur && operateur.signature_base64){
          var wrapper = document.getElementById('result-signature-'+key);
          $(wrapper).removeClass('ng-hide');
          $(wrapper).find('img').attr('src',operateur.signature_base64);
        }
    }

    $scope.precedent = function() {
        $scope.updateActive('recapitulatif');
    }

    $scope.updateProduit = function(prelevement) {
        prelevement.libelle = prelevement.produit.libelle;
        prelevement.libelle_produit = prelevement.produit.libelle_produit;
        prelevement.hash_produit = prelevement.produit.hash_produit;
        prelevement.vtsgn = prelevement.produit.vtsgn;

        var code_cepage = prelevement.hash_produit.substr(-2);

        if(prelevement.vtsgn) {
            code_cepage += prelevement.vtsgn;
        }
        prelevement.anonymat_prelevement_complet = prelevement.anonymat_prelevement_complet.replace(new RegExp("^[a-zA-Z0-9_]+ "), code_cepage + " ");
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

    $scope.signature = function(operateur, id) {
        $scope.valide(operateur);

        if(operateur.has_erreurs) {

            return;
        }

        $scope.precedent(operateur);

        localSave();
        operateur.transmission_needed = true;

        var wrapper = document.getElementById(id);
        $(wrapper).removeClass('ng-hide');
        canvas = wrapper.querySelector("canvas");

        canvas.width = canvas.clientWidth;
        signaturePad = new SignaturePad(canvas);
        if (operateur.signature_base64) {
          signaturePad.fromDataURL(operateur.signature_base64);
        }

        wrapper.querySelector('.signature-pad-clear').onclick = function() {
            if(confirm("Étes-vous sûr de vouloir effacer la signature ?")) {
                signaturePad.clear();
            }
        };

        $scope.active = 'signature_'+ operateur._id;
    }

    $scope.signerRevenir = function(operateur) {

      $scope.valide(operateur);
      operateur.signature_base64 = null;
      if (!signaturePad.isEmpty()) {
          operateur.signature_base64 = signaturePad.toDataURL();
      }
      $scope.terminer(operateur);
      $scope.updateActive(operateur._id);
      $scope.active = operateur._id;
    }

    $scope.valide = function(operateur) {
        operateur.termine = false;
        operateur.has_erreurs = false;
        operateur.erreurs = [];
        var nb = 0;
        var nb_prelevements = 0;
        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            prelevement.erreurs = [];

            if(operateur.aucun_prelevement && operateur.motif_non_prelevement) {
                prelevement.preleve = 0;
                prelevement.motif_non_prelevement = null;
            }

            if(prelevement.preleve && !prelevement.cuve) {
                prelevement.erreurs['cuve'] = true;
                operateur.has_erreurs = true;
            }
            if(prelevement.preleve && !prelevement.hash_produit) {
                prelevement.erreurs['hash_produit'] = true;
                operateur.has_erreurs = true;
            }
            if(!operateur.aucun_prelevement && !prelevement.preleve && prelevement.hash_produit && !prelevement.motif_non_prelevement) {
                prelevement.erreurs['motif'] = true;
                operateur.has_erreurs = true;
            }
            if(prelevement.preleve) {
                nb++;
                nb_prelevements++;
            }

            if(!prelevement.preleve && prelevement.motif_non_prelevement) {
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

        operateur.nb_prelevements = nb_prelevements;

        localSave();

        if(operateur.has_erreurs) {

            return;
        }

        if(!operateur.aucun_prelevement && operateur.motif_non_prelevement) {
            operateur.motif_non_prelevement = null;
        }

        for(prelevement_key in operateur.prelevements) {
            var prelevement = operateur.prelevements[prelevement_key];
            if(!prelevement.preleve && prelevement.motif_non_prelevement == "REPORT") {
                operateur.motif_non_prelevement = "REPORT";
            }
        }

        if(!nb_prelevements && nb && !operateur.motif_non_prelevement) {
            for(prelevement_key in operateur.prelevements) {
               var prelevement = operateur.prelevements[prelevement_key];
               if(operateur.motif_non_prelevement && prelevement.motif_non_prelevement != operateur.motif_non_prelevement) {
                    operateur.motif_non_prelevement = "MIXTE";
               } else {
                    operateur.motif_non_prelevement = prelevement.motif_non_prelevement;
               }
            }
        }

        if(!nb_prelevements && nb && !operateur.motif_non_prelevement) {
            operateur.motif_non_prelevement = "MIXTE";
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
            prelevement.motif_non_prelevement = null;
        }

        $scope.updatePreleve(prelevement);
    }

    $scope.toggleOperateurAucun = function(operateur) {
        if(operateur.erreurs) {
            operateur.erreurs["aucun_prelevement"] = false;
        }
        if(operateur.aucun_prelevement) {
            operateur.aucun_prelevement = 0;
            operateur.motif_non_prelevement = null;
        } else {
            operateur.aucun_prelevement = 1;
        }
        localSave();
    }

    $scope.updateMotif = function(operateur) {
        if(operateur.erreurs) {
            operateur.erreurs["motif"] = false;
        }
        localSave();
    }

    $scope.updateMotifPrelevement = function(operateur) {

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

        if(!auto) {
            if(!confirm("Êtes vous sur de vouloir transmettre les données ?")) {

                return;
            }
        }

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = "success";

        var degustations = $scope.degustations;

        if(auto) {
            var degustations = getDegustationsToTransmettre();
        }

        remoteSave(degustations, function(data) {
            if(!auto) {
                $scope.transmission = true;
            }

            $scope.transmission_progress = false;

            if(data === true) {
                $scope.transmission_result = "aucune_transmission";
                return;
            }

           if(!data) {
               $scope.transmission_result = "error";
               $scope.testState();
               return;
            }

            if(typeof data !== 'object') {
                $scope.transmission_result = "error";
                $scope.testState();
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
    }, 5000);

    setInterval(function() {
        $scope.loadOrUpdateDegustations();
    }, 15000);

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

    $scope.getCodeCepageNumero = function(numero) {

        return numero.replace(/ .+ .+$/, "");
    }

    $scope.getIncrementalNumero = function(numero) {

        return numero.replace(/^.+ (.+) .+$/, '$1');
    }

    $scope.getCodeVerifNumero = function(numero) {

        return numero.replace(/^.+ (.+) /, "");
    }

    $scope.getMoyennePrelevements = function() {

        return (Math.round($scope.prelevements.length/$scope.commissions.length*10)/10)
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
        $scope.transmettre(true);
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
        localSave();
        $scope.showAjout(commission);
    }

}]);

myApp.controller('degustationCtrl', ['$scope', '$rootScope', '$http', 'localStorageService', '$filter', function($scope, $rootScope, $http, localStorageService, $filter) {

    $scope.active = 'recapitulatif';
    $scope.transmission = false;
    $scope.transmission_progress = false;
    $scope.state = true;
    $scope.prelevements = [];
    $scope.loaded = false;
    $scope.notes_key = Object.keys($scope.notes);
    $scope.ajout_defaut = {
        query: null,
        prelevement: null,
        note: null
    }

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
                    updateDegustationFromLoad(degustationRemote);
                    modified = true;
                } else {
                    $scope.degustations[degustationRemote._id] = degustationRemote;
                    updateDegustationFromLoad(degustationRemote);
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

    var updateDegustationFromLoad = function(degustation) {
        for(prelevement_key in degustation.prelevements) {
            var prelevement = degustation.prelevements[prelevement_key];
            if($scope.isNonSaisie(prelevement)) {
                prelevement.termine = false;
            } else {
                $scope.isValide(prelevement);
            }
        }
    }

    var updatePrelevements = function() {
        $scope.prelevements = [];
        for(degustation_key in $scope.degustations) {
            var degustation = $scope.degustations[degustation_key];
            for(prelevement_key in degustation.prelevements) {
                var prelevement = degustation.prelevements[prelevement_key];
                if(prelevement.commission == $scope.commission) {
                    prelevement.degustation_id = degustation._id;
                    $scope.prelevements.push($scope.degustations[degustation_key].prelevements[prelevement_key]);
                }
            }
        }
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

        if(!auto) {
            if(!confirm("Êtes vous sur de vouloir transmettre les données ?")) {

                return;
            }
        }

        $scope.transmission = false;
        $scope.transmission_progress = true;
        $scope.transmission_result = "success";

        var degustations = $scope.degustations;

        if(auto) {
            var degustations = getDegustationsToTransmettre();
        }

        remoteSave(degustations, function(data) {
            if(!auto) {
                $scope.transmission = true;
            }

            $scope.transmission_progress = false;

            if(data === true) {
                $scope.transmission_result = "aucune_transmission";
                return;
            }

            if(!data) {
               $scope.transmission_result = "error";
               $scope.testState();
               return;
            }

            if(typeof data !== 'object') {
                $scope.transmission_result = "error";
                $scope.testState();
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

    $scope.precedent = function() {
        $scope.showRecap();
    }

    $scope.showRecap = function() {
        $scope.active = 'recapitulatif';
    }

    $scope.showNext = function(prelevement) {
        var finded = false;
        var prelevements_sorted = $filter('orderBy')($scope.prelevements, ['anonymat_degustation']);
        for (prelevement_key in prelevements_sorted) {
            if(finded) {
                $scope.showCepage(prelevements_sorted[prelevement_key]);
                return;
            }
            if(prelevements_sorted[prelevement_key].anonymat_degustation == prelevement.anonymat_degustation) {
                finded = true;

            }
        }

        $scope.showRecap();
    }

    $scope.showCepage = function(prelevement) {
        $scope.active = 'cepage_' + prelevement.anonymat_degustation + prelevement.degustation_id + prelevement.hash_produit;
        $scope.transmission = false;
        if(!$('.select2-input').length) {
            $('.select2autocomplete').select2({allowClear: true, placeholder: true, openOnEnter: true});
        }
    }

    $scope.showAjoutDefaut = function(prelevement, note_key) {
        $scope.active = 'ajout_defaut';
        $scope.ajout_defaut.query = "";
        $scope.ajout_defaut.prelevement = prelevement;
        $scope.ajout_defaut.note_key = note_key;
    }

    $scope.removeDefaut = function(prelevement, note_key, defaut) {

        if (!confirm("Etes vous sûr de vouloir supprimer ce défaut") == true) {

            return;
        }

        var indexDefaut = prelevement.notes[note_key].defauts.indexOf(defaut);

        if(indexDefaut === -1) {
            return;
        }

        prelevement.notes[note_key].defauts.splice(indexDefaut, 1);
        localSave();
    }

    $scope.ajouterDefaut = function(prelevement, note_key, defaut) {
        if(prelevement.notes[note_key].defauts.indexOf(defaut) === -1) {
            prelevement.notes[note_key].defauts.push(defaut);
        }
        $scope.showCepage(prelevement);
        localSave();
    }

    $scope.isNonSaisie = function(prelevement) {
        for(key_note in prelevement.notes) {
            var note = prelevement.notes[key_note];
            if(note.note !== null) {
                return false;
            }
        }

        return true;
    }

    $scope.isValide = function(prelevement) {
        prelevement.has_erreurs = false;
        prelevement.erreurs = [];
        for(key_note in prelevement.notes) {
            var note = prelevement.notes[key_note];
            note.erreurs = [];
            note.has_erreurs = false;
            if(note.note === null) {
                note.has_erreurs = true;
                note.erreurs['requis'] = true;
                prelevement.has_erreurs = true;
                prelevement.erreurs["requis"] = true;
            }

            if(note.note.match(/[012CD]+/) && note.defauts.length == 0) {
                note.has_erreurs = true;
                note.erreurs["defaut"] = true;
                prelevement.has_erreurs = true;
                prelevement.erreurs["defaut"] = true;
            }
        }

        if(prelevement.has_erreurs) {

            return;
        }

        prelevement.termine = true;
    }

    $scope.valider = function(prelevement) {
        $scope.isValide(prelevement);

        if(prelevement.has_erreurs) {
            localSave();
            return;
        }

        $scope.degustations[prelevement.degustation_id].transmission_needed = true;
        localSave();
        $scope.transmettre(true);
        $scope.showNext(prelevement);
    }

}]);
