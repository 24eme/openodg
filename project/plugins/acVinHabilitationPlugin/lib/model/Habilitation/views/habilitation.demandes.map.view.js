function(doc) {
    if(doc.type != "Habilitation") {

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    for(demandeKey in doc.demandes) {
        var demande = doc.demandes[demandeKey];

        emit([demande.statut, demande.demande, demande.produit_libelle, demande.libelle, demande.date, demande.date_habilitation, demandeKey, doc.identifiant], 1);
    }
}
