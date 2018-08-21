function(doc) {
     if(doc.type != "Habilitation") {

         return;
     }

     for(demandeKey in doc.demandes) {
         var demande = doc.demandes[demandeKey];
         emit([demande.date, demande.statut, demande.produit_libelle, demande.libelle, demande.date_habilitation, demandeKey, demande.demande, doc.identifiant], 1);
     }
 }
