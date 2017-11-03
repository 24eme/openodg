function(doc) {
    if(doc.type != "Habilitation") {

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    for(hash in doc.declaration) {
	    var produit = doc.declaration[hash];

        for(activiteKey in produit.activites) {
            var activite = produit.activites[activiteKey];
	        if(activite.statut) {
            	emit([activite.statut, activiteKey, produit.libelle, activite.date, doc.identifiant, doc.declarant.raison_sociale, doc.declarant.cvi, hash], 1);
            }
        }
    }
}
