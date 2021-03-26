function(doc) {
      if(!doc.mouvements){
        return;
      }
     for(identifiant in doc.mouvements) {
         for(key in doc.mouvements[identifiant]) {
             var mouv = doc.mouvements[identifiant][key];
             emit([mouv.facture, mouv.facturable, identifiant, mouv.detail_identifiant, doc.type], mouv);
           }
    }
 }
