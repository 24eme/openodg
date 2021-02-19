function(doc) {

     if(doc.type != "DRev" && doc.type != "Conditionnement" && doc.type != "Transaction" && doc.type != "RegistreVCI" && doc.type != "DRevMarc" && doc.type != "ParcellaireAffectation" && doc.type != "Tirage" && doc.type != "TravauxMarc" && doc.type != "ParcellaireIrrigable") {

         return;
     }

     if(doc.lecture_seule) {

         return;
     }

     emit([doc.identifiant, doc.campagne, doc.type], 1);
 }
