function(doc) {
     if(doc.type != "Habilitation") {

         return;
     }

     if(doc.lecture_seule) {

         return;
     }
     for(historiqueKey in doc.historique) {
         var historique = doc.historique[historiqueKey];
         emit([historique.date, historique.statut, doc.identifiant, historique.description, historique.commentaire, historique.auteur, historique.iddoc], 1);
     }
 }
