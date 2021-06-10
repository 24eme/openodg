function(doc) {
       if(doc.type != "DRev" && doc.type != "RegistreVCI" && doc.type != "DRevMarc" && doc.type != "ParcellaireAffectation" && doc.type != "ParcellaireIntentionAffectation" && doc.type != "Tirage" && doc.type != "TravauxMarc" && doc.type != "ParcellaireIrrigable"  && doc.type != "ParcellaireIrrigue" && doc.type != "Habilitation" && doc.type != "DR" && doc.type != "SV12" && doc.type != "SV11" && doc.type != "Facture" && doc.type != "RegistreVCI" && doc.type != "Degustation" && doc.type != "Conditionnement" && doc.type != "Transaction") {
           return;
       }
       if(doc.lecture_seule) {
           return;
       }
       var campagne = (doc.campagne) ? doc.campagne : "TOUT";
       if (doc.type == "ParcellaireIntentionAffectation") {
    	   campagne = "TOUT";
       }
       emit([doc.type, campagne, doc.identifiant], 1);
   }
