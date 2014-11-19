function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc") {
        
        return;
    }
    
    emit([doc.type, doc.campagne, doc.validation, doc.validation_odg, doc.etape, doc.identifiant, doc.declarant.nom], 1);
}