function(doc) {

      if(doc.type != "DRev" && doc.type != "RegistreVCI" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Tirage" && doc.type != "TravauxMarc") {
          return;
      }

      if(doc.lecture_seule) {

          return;
      }

      var campagne = (doc.campagne) ? doc.campagne : "TOUT";

      emit([doc.type, campagne, doc.identifiant], 1);
  }
