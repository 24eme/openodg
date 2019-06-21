function(doc) {
    if (!doc.pieces) {
        return;
    }

    for(key in doc.pieces) {
    	var piece = doc.pieces[key];
        var categorie = piece.categorie;
        if(!categorie) {
            categorie = doc._id.replace(/-.+/, '').toLowerCase();
        }
        emit([piece.visibilite, piece.identifiant, piece.date_depot, categorie, piece.libelle, piece.mime, piece.source], [key, piece.fichiers]);
    }
}
