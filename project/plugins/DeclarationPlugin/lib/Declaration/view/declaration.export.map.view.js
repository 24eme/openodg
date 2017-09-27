function(doc) {
 
     if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Constats" && doc.type != "Facture" && doc.type != "Tirage" && doc.type != "DR" && doc.type != "SV11" && doc.type != "SV12") {
 
         return;
     }
 
     if(doc.lecture_seule) {
 
         return;
     }
 
     emit([doc.type, doc.campagne, doc.identifiant], 1);
 }
