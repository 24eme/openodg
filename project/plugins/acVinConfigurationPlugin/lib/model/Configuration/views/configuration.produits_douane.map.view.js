function(doc) {
    if (doc.type != "Configuration")
    return ;

    var inter = new Array();
    var code_douane = null;

    for (var c in doc.declaration.certifications) {
        var interpros = new Array();
        for (var interpro_key in doc.declaration.certifications[c].interpro) {
            interpros.push(interpro_key);
        }
        inter.unshift(interpros);
        if (!code_douane) { code_douane = doc.declaration.certifications[c].code_douane; }
        for (var g in doc.declaration.certifications[c].genres) {
            var interpros = new Array();
            for (var interpro_key in doc.declaration.certifications[c].genres[g].interpro) {
                interpros.push(interpro_key);
            }
            inter.unshift(interpros);
            if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].code_douane; }
            for (var a in doc.declaration.certifications[c].genres[g].appellations) {
                var interpros = new Array();
                for (var interpro_key in doc.declaration.certifications[c].genres[g].appellations[a].interpro) {
                    interpros.push(interpro_key);
                }
                inter.unshift(interpros);
                if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].appellations[a].code_douane; }
                for (var m in doc.declaration.certifications[c].genres[g].appellations[a].mentions) {
                    if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].code_douane; }
                    for (var l in doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux) {
                        if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux[l].code_douane; }
                        for (var co in doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux[l].couleurs) {
                            if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux[l].couleurs[co].code_douane; }
                            for (var ce in doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux[l].couleurs[co].cepages) {
                                if (!code_douane) { code_douane = doc.declaration.certifications[c].genres[g].appellations[a].mentions[m].lieux[l].couleurs[co].cepages[ce].code_douane; }
                                var hash = "/declaration/certifications/"+c+"/genres/"+g+"/appellations/"+a+"/mentions/"+m+"/lieux/"+l+"/couleurs/"+co+"/cepages/"+ce;
                                for (var i in inter) {
                                    if (inter[i].length > 0) {
                                        for (var array_intepro_key in inter) {
                                            emit([inter[i][array_intepro_key], code_douane, hash], null);         
                                            break;
                                        }
                                        break;
                                    }
                                }
                                code_douane = null;
                            }
                        }
                    }
                }
                inter.splice(0,1);
            }
            inter.splice(0,1);
        }
        inter.splice(0,1);
    }
}