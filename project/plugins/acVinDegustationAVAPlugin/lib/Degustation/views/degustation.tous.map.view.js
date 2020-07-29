function(doc) {
    if(doc.type != "Degustation") {
        return;
    }
    
    if(!doc.date_degustation) {
        return;
    }
    
    var statut = "DEGUSTE";
    
    if(doc.motif_non_prelevement) {
        statut = doc.motif_non_prelevement;
    }

    if(statut == "DEGUSTE" && !doc.prelevements.length) {
        return;
    }

    emit([doc.appellation, doc.identifiant, doc.date_degustation, statut, doc.drev], doc.prelevements.length);
}