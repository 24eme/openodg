function(doc) {

     if(
         doc.type != "ChgtDenom" &&
         doc.type != "Conditionnement" &&
         doc.type != "DRev" &&
         doc.type != "DR" &&
         doc.type != "DRevMarc" &&
         doc.type != "ParcellaireManquant" &&
         doc.type != "ParcellaireAffectation" &&
         doc.type != "ParcellaireIrrigable" &&
         doc.type != "ParcellaireIrrigue" &&
         doc.type != "RegistreVCI" &&
         doc.type != "Tirage" &&
         doc.type != "Transaction" &&
         doc.type != "TravauxMarc" &&
         doc.type != "Degustation" &&
         doc.type != "PMC" &&
         doc.type != "PMCNC" &&
         doc.type != "Adelphe"
     ) {
         return;
     }

     if (doc.type == "Degustation") {
       if (doc.etape != "VISUALISATION" || !doc.region) {
         return;
       }

       if (!doc.validation_oi) {
         return;
       }
     }

     if(!doc.campagne) {
         return;
     }

     campagne = doc.campagne;
     if (campagne.length == 4) {
       campagne = campagne;
       campagneplusun = parseInt(campagne) + 1;
       campagne = campagne + "-" + campagneplusun;
     }

     if(!doc.declarant && doc.type != "Degustation") {

         return;
     }

     if(doc.lecture_seule) {

         return;
     }

     var nb_doc_en_attente = 0;

     if(doc.documents) {
         for(key in doc.documents) {
             if(doc.documents[key].statut != "RECU") {
                 nb_doc_en_attente++;
             }
         }
     }

     var validation = null;
     if(doc.validation) {
     validation = doc.validation;
     }

     if (! validation && doc.date_import) {
       validation = doc.date_import;
     }

     if (! validation && doc.date_depot) {
       validation = doc.date_depot;
     }

     var validation_organisme = null;
     if(doc.validation_odg) {
     validation_organisme = doc.validation_odg;
     }
     if(doc.validation_oi) {
     validation_organisme = doc.validation_oi;
     }

     var etape = null;
     if(doc.etape) {
     etape = doc.etape;
     }

     var papier = 0;
     if(doc.papier) {
         papier = 1;
     }

     var automatique = 0;
     if(doc.automatique) {
         automatique = 1;
     }

     var raison_sociale = null;
     if(doc.declarant && doc.declarant.raison_sociale) {
         raison_sociale = doc.declarant.raison_sociale;
     }

     if(doc.type == "Degustation") {
         raison_sociale = doc.region.split('|')[0]
     }

     var commune = null;
     if(doc.declarant && doc.declarant.commune) {
         commune = doc.declarant.commune;
     }

     var email = null;
     if(doc.declarant && doc.declarant.email) {
         email = doc.declarant.email;
     }

     var cvi = null;
     if(doc.declarant && doc.declarant.cvi) {
         cvi = doc.declarant.cvi;
     }

     var mode = "Télédeclaration";

     if(doc.automatique) {
         mode = "Importé";
     }

     if(doc.papier || doc.type == "Degustation") {
         mode = "Saisie interne";
     }

     var statut = "Brouillon";
     var infos = "Étape " + doc.etape;
     if(validation_organisme) {
 	    statut = "Approuvé";
         infos = null;
         if(validation_organisme !== false && validation_organisme !== true) {
             infos = validation_organisme.replace(/([0-9]+)-([0-9]+)-([0-9]+)(T.*)?/, "$3/$2/$1");
         }
     }

     if(validation && !validation_organisme) {
 	    statut = "À approuver";
         infos = null;
         if(nb_doc_en_attente) {
             statutProduit = "En attente";
            infos = nb_doc_en_attente + " pièce(s) en attente";
 	    }
     }

      if (doc.statut_odg) {
        statut = doc.statut_odg;
      }
      regions = [];
      if (doc.region) {
          regions = doc.region.split('|');
      }

     var type = doc.type;

     var date = null;
     if(doc.date) {
 	    date = doc.date;
     }
     if(validation && validation !== false && validation !== true) {
 	    date = validation;
     }

     if(doc._id.indexOf('PARCELLAIREAFFECTATIONCREMANT') > -1) {
 	    type = "Affectation Crémant";
     }
     if(doc._id.indexOf('PARCELLAIREAFFECTATION') > -1) {
 	    type = "Affectation";
     }
     if(doc._id.indexOf('INTENTIONCREMANT') > -1) {
 	    type = "Intention Crémant";
     }
     if(doc._id.indexOf('PARCELLAIREMANQUANT') > -1) {
 	    type = "Manquant";
     }
     var statutProduit = statut;
     var nb_emits = 0;
     if(doc.type == "DRev" && !doc.declaration.certification){
            for (key in doc.declaration) {

              if (key.toLowerCase().includes("igp")) {
                continue;
              }
               for(detailKey in doc.declaration[key]){
                 if(doc.declaration[key][detailKey].validation_odg){
                   statutProduit = "Approuvé";
                 }
                 if(doc.declaration[key][detailKey].statut_odg){
                    statutProduit = doc.declaration[key][detailKey].statut_odg;
                 }
                 emit(['', type, campagne, doc.identifiant, mode, statutProduit, key, date, infos, raison_sociale, commune, email, cvi], 1);
                 for (regionid in regions) {
                     if (regions[regionid].toLowerCase().includes("igp")) {
                       continue;
                     }
                    emit([regions[regionid], type, campagne, doc.identifiant, mode, statutProduit, key, date, infos, raison_sociale, commune, email, cvi], 1);
                 }
                 nb_emits = nb_emits + 1;
               }
            }
     }

    if(doc.lots){
      statutProduit = statut;
      var produitsHash = [];

      for(lotKey in doc.lots) {
        var lot = doc.lots[lotKey];
        if(lot.produit_hash) {
          var pHash = lot.produit_hash.replace('/declaration/', '');
          produitsHash[pHash] = pHash;
        }
      }

      for(produitHash in produitsHash) {
        statutProduit = statut;

        if (doc.declaration && doc.declaration[produitHash]) {
          for(detailKey in doc.declaration[produitHash]) {
            if(doc.declaration[produitHash][detailKey].validation_odg){
              statutProduit = "Approuvé";
            }
            if(doc.declaration[produitHash][detailKey].statut_odg){
              statutProduit = doc.declaration[produitHash][detailKey].statut_odg;
            }
          }
        }

        emit(['', type, campagne, doc.identifiant, mode, statutProduit, produitHash, date, infos, raison_sociale, commune, email, cvi], 1);
        for (regionid in regions) {
          if (regions[regionid].toLowerCase().includes("aop")) {
            continue;
          }
          emit([regions[regionid], type, campagne, doc.identifiant, mode, statutProduit, produitHash, date, infos, raison_sociale, commune, email, cvi], 1);
        }

        nb_emits = nb_emits + 1;
      }
    }

     if(!nb_emits){
         emit(['', type, campagne, doc.identifiant, mode, statut, null, date, infos, raison_sociale, commune, email, cvi], 1);
         for (regionid in regions) {
             emit([regions[regionid], type, campagne, doc.identifiant, mode, statut, null, date, infos, raison_sociale, commune, email, cvi], 1);
         }
     }
 }
