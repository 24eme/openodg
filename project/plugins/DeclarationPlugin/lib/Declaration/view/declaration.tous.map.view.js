function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Constats") {
        
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

    var validation = null;
    if(doc.validation) {
    validation = doc.validation;
    }
 
    var validation_odg = null;
    if(doc.validation_odg) {
    validation_odg = doc.validation_odg;
    }

    var etape = null;
    if(doc.etape) {
    etape = doc.etape;
    }

    var papier = 0;
    if(doc.papier) {
        papier = 1;
    }

    var automatique = 0;
    if(doc.automatique) {
        automatique = 1;
    }

    var raison_sociale = null;
    if(doc.declarant && doc.declarant.raison_sociale) {
        raison_sociale = doc.declarant.raison_sociale;
    }

    var commune = null;
    if(doc.declarant && doc.declarant.commune) {
        commune = doc.declarant.commune;
    }

    var email = null;
    if(doc.declarant && doc.declarant.email) {
        email = doc.declarant.email;
    }
    
    emit([doc.type, doc.campagne, validation, validation_odg, etape, doc.identifiant, nb_doc_en_attente, papier, automatique, raison_sociale, commune, email], 1);
}