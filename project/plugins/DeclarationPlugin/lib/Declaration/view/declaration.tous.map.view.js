function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire") {
        
        return;
    }

    var nb_doc_en_attente = 0;
    
    if(doc.documents) {
        for(key in doc.documents) {
            if(doc.documents[key].statut != "RECU") {
                nb_doc_en_attente++;
            }
        }
    }

    var papier = 0;

    if(doc.papier) {
        papier = 1;
    }

    var automatique = 0;

    if(doc.automatique) {
        automatique = 1;
    }

    emit([doc.type, doc.campagne, doc.validation, doc.validation_odg, doc.etape, doc.identifiant, nb_doc_en_attente, papier, automatique, doc.declarant.raison_sociale, doc.declarant.commune, doc.declarant.email], 1);
}