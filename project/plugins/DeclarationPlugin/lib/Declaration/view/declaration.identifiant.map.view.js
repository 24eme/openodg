function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Tirage") {

        return;
    }

    if(doc.lecture_seule) {

        return;
    }

    emit([doc.identifiant, doc.campagne, doc.type], 1);
}
