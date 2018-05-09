function(doc) {

      if(doc.type != "DRev" && doc.type != "RegistreVCI" && doc.type != "DRevMarc" && doc.type != "ParcellaireAffectation" && doc.type != "Tirage" && doc.type != "TravauxMarc" && doc.type != "ParcellaireIrrigable" && doc.type != "Habilitation") {
          return;
      }

      if(doc.lecture_seule) {

          return;
      }

      var campagne = (doc.campagne) ? doc.campagne : "TOUT";

      emit([doc.type, campagne, doc.identifiant], 1);
  }
