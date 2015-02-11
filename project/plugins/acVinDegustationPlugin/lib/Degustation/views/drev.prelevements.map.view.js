function(doc) {

    if(doc.type != "DRev") {
        
        return;
    }

    if(!doc.validation) {
        return;
    }

    for(key in doc.prelevements) {
        var prelevement = doc.prelevements[key];
        if(prelevement.date) {
            for(key_lot in prelevement.lots) {
                var lot = prelevement.lots[key_lot];
                emit([key, prelevement.date, doc.identifiant, doc.declarant.raison_sociale, doc.declarant.commune, lot.hash_produit, lot.libelle], lot.nb_hors_vtsgn);
            }
        }
    }
}