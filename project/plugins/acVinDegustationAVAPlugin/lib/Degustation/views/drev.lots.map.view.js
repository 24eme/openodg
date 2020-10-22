function(doc) {

    if(doc.type != "DRev") {

        return;
    }


    if(doc.lecture_seule) {

        return;
    }

    if(!doc.validation_odg) {
        return;
    }

    for(key in doc.prelevements) {
        var prelevement = doc.prelevements[key];
        chai = doc.chais['cuve_'];
    	for(lotKey in prelevement.lots) {
    		var lot = prelevement.lots[lotKey];
                	emit([doc.campagne, key, lot.hash_produit, lot.vtsgn, doc.identifiant], { raison_sociale: doc.declarant.raison_sociale, adresse: chai.adresse, code_postal: chai.code_postal, commune: chai.commune, produit_libelle: lot.libelle});
    	}
    }
}
