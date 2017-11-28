function(doc) {

      if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Constats" && doc.type != "Facture" && doc.type != "Tirage" && doc.type != "DR" && doc.type != "SV11" && doc.type != "SV12" && doc.type != "Habilitation" && doc.type != "TravauxMarc") {

          return;
      }

      if(doc.lecture_seule) {

          return;
      }

      var campagne = (doc.campagne) ? doc.campagne : "TOUT";

      emit([doc.type, campagne, doc.identifiant], 1);
  }
