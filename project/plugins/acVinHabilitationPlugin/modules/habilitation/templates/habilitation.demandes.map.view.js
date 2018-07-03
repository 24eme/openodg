function(doc) {
    if(doc.type != "Habilitation") {

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    for(demandeKey in doc.demandes) {
        var demande = doc.demandes[demandeKey];
    	emit([demande.statut, demandeKey, demande.libelle], 1);
    }
}
