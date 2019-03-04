function(doc) {
    if(doc.type != "Habilitation") {

        return;
    }
    var r = RegExp("^([A-Z0-9\-]+)\:\/demandes\/([0-9\-]+)$");
    for(historiqueKey in doc.historique) {
        var historique = doc.historique[historiqueKey];
        var m = (historique.iddoc).match(r);
        if (m) {
        	var d = doc.demandes[m[2]];
        	emit([historique.date, historique.statut, doc.identifiant, historique.description, historique.commentaire, historique.auteur, historique.iddoc, d.demande, d.date_habilitation, d.produit, d.produit_libelle, d.activites], 1);
        } else {
        	emit([historique.date, historique.statut, doc.identifiant, historique.description, historique.commentaire, historique.auteur, historique.iddoc, null, null, null, null, null], 1);
        }
    }
}
