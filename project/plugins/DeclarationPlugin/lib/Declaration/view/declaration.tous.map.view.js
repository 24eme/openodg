function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc") {
        
        return;
    }

    nb_doc_en_attente = 0;
    
    if(doc.documents) {
        for(key in doc.documents) {
            if(doc.documents[key].statut != "RECU") {
                nb_doc_en_attente++;
            }
        }
    }

    emit([doc.type, doc.campagne, doc.validation, doc.validation_odg, doc.etape, doc.identifiant, nb_doc_en_attente, doc.declarant.nom], 1);
}