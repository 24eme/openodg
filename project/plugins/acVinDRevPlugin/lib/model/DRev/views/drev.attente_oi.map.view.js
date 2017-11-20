function(doc) {
     if(doc.type != "DRev") {
         return;
     }
     if(doc.envoi_oi) {
         return;
     }
     if(!doc.validation_odg) {
         return;
     }
     emit([doc.campagne, doc.identifiant], 1);
 }