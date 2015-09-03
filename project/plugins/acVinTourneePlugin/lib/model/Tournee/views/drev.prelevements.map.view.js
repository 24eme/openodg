function(doc) {

    if(doc.type != "DRev") {
        
        return;
    }

    if(!doc.validation) {
        return;
    }

    for(key in doc.prelevements) {
        var prelevement = doc.prelevements[key];
        chai = doc.chais['cuve_'];
        if(prelevement.date) {
            emit([key, prelevement.date, doc.identifiant, doc.declarant.raison_sociale, chai.adresse, chai.code_postal, chai.commune], prelevement.total_lots);
        }
    }
}