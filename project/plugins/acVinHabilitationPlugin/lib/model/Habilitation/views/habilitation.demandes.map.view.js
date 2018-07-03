function(doc) {
    if(doc.type != "Habilitation") {

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    for(demandeKey in doc.demandes) {
        var demande = doc.demandes[demandeKey];

        if(demande.statut != "VALIDE") {
            emit([demande.demande, demande.statut, demande.libelle, demande.date, demandeKey, doc.identifiant], 1);
        }
    }
}
