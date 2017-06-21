function(doc) {

    if(doc.type != "DRev" && doc.type != "DRevMarc" && doc.type != "Parcellaire" && doc.type != "Tirage") {

        return;
    }

    if(doc.lecture_seule) {

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

    var mode = "Télédeclaration";

    if(doc.automatique) {
        mode = "Importé";

        return;
    }

    if(doc.papier) {
        mode = "Saisie interne";
    }

    var statut = "Brouillon";
    var infos = "Étape " + doc.etape;
    if(validation_odg) {
	statut = "Validé";
        infos = null;
    }

    if(validation && !validation_odg) {
	statut = "À valider";
        infos = null;
        if(nb_doc_en_attente) {
           infos = nb_doc_en_attente + " pièce(s) en attente";
	}
    }

    var type = doc.type;

    var date = null;
    if(validation_odg) {
	date = validation_odg;
    }

    var date = null;

    if(validation && validation !== false && validation !== true) {
	date = validation;
    }

    if(validation_odg && validation_odg !== false && validation_odg !== true) {
	date = validation_odg;
    }

    if(doc._id.indexOf('PARCELLAIRECREMANT') > -1) {
	type = "Parcellaire Crémant";
    }

    emit([type, doc.campagne, mode, statut, etape, doc.identifiant, date, infos, raison_sociale, commune, email], 1);
}
