var fs = require('fs');
var fileHTML = process.argv[2];
const cheerio = require('cheerio');
const $ = cheerio.load(fs.readFileSync(fileHTML));
const readFileLinesSync = path => fs.readFileSync(path, 'UTF8').toString().split('\n');


emails = {};
tvas = {}
ccomptables = {}

if (process.argv[3]) {
  lines = readFileLinesSync(process.argv[3]);
  lines.forEach(function(e){
    t = e.split(";");
    if (t[7]) {
      siren = t[7].substring(0, 9);
      if (t[6]) {
        emails[siren] = t[6];
      }
      if (t[8]) {
        tvas[siren] = t[8];
      }
      if (t[0]) {
        ccomptables[siren] = t[0];
      }
    }
  });
}


if (process.argv[4]) {
  lines = readFileLinesSync(process.argv[4]);
  lines.forEach(function(e){
    t = e.split(";");
    if (t[15] && t[7]) {
      emails[t[7]] = t[15];
    }
  });
}

ent = fileHTML.replace(/.*ENT/, 'ENT').replace('_identite.html', '');

trs = $('table.fiche-identite-table td')
raisonsociale = $(trs[1]).text().trim();
nomentreprise = $(trs[3]).text().trim();
siret = $(trs[5]).text().trim();
cvi = $(trs[7]).text().trim();
ppm = $(trs[9]).text().trim();
accise = $(trs[11]).text().trim();
tva = $(trs[13]).text().trim();
adresse = $(trs[15]).find('input').prop('value');
if (adresse) {
  adresse = adresse.trim();
}
codepostal = $(trs[19]).find('input').prop('value').trim();
ville = $(trs[21]).find('span').first().text().trim();
tel = $('#ctl00_ContentPlaceHolder1_tbTel').prop('value') + '';
if (tel) {
  tel = tel.replace('__.__.__.__.__', '').replace(/\n/, ' ').replace(/\s\s*/, ' ').trim();
}else{
  tel = '';
}
fax = $('#ctl00_ContentPlaceHolder1_tbFax').prop('value') + '';
if (fax) {
  fax = fax.replace('__.__.__.__.__', '').replace(/\n/, ' ').replace(/\s\s*/, ' ').trim();
}else{
  fax = '';
}
portable = $('#ctl00_ContentPlaceHolder1_tbPortable').prop('value') + '';
if (portable) {
  portable = portable.replace('__.__.__.__.__', '').replace(/\n/, ' ').replace(/\s\s*/, ' ').trim();
}else{
  portable = '';
}
courriel = '';
if ( $('#ctl00_ContentPlaceHolder1_tbCourriel').hasOwnProperty('value') ) {
courriel = $('#ctl00_ContentPlaceHolder1_tbCourriel').prop('value').replace(';', ' ');
}

habilitations = []

siren = siret.substring(0, 9)
if (!tva && tvas[siren]) {
  tva = tvas[siren];
}
if (!courriel && emails[siren]) {
  courriel = emails[siren];
}
if (!courriel && emails[cvi]) {
  courriel = emails[cvi];
}
code_copmtable = '';
if (ccomptables[siren]) {
  code_copmtable = ccomptables[siren];
}


$('#ctl00_ContentPlaceHolder1_gvlstHabOp tr').each( (h_idx, h) => {
  tds = $(h).find('td');
  if (!$(tds[1]).text()) {
    return;
  }
  habilitations.push(['HABILITATION', ent, raisonsociale, nomentreprise, siret, cvi, ppm, accise, tva, adresse, codepostal, ville, tel, fax, portable, courriel, $(tds[1]).text(), $(tds[0]).text(), $(tds[2]).text(), code_copmtable]);
});

$('#ctl00_ContentPlaceHolder1_gvSitesStockages tr').each( (stk_idx, s) => {
  tds = $(s).find('td');
  if (!$(tds[1]).text()) {
    return;
  }

  codepostalville = $(tds[3]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' ');
  codepostal = codepostalville.replace(/ .*/, '');
  ville = codepostalville.replace(/^[0-9]* /, '');
  tels = $(tds[4]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' ');

  habilitations.push(['CHAIS', ent,
                      $(tds[0]).text().replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim(), // CSV_CHAIS_ACTIVITE
                      $(tds[1]).text().replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim(), //CSV_CHAIS_SITE
                      $(tds[2]).text().replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim(), // CSV_CHAIS_ADRESSE
                      codepostal.replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim(), // CSV_CHAIS_CODEPOSTAL
                      ville.replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim(), //CSV_CHAIS_VILLE
                      tels.replaceAll("\n", ' ').replace(/\s\s*/, ' ').trim() // CSV_CHAIS_TEL
                    ]);
});

for (i in habilitations) {
  console.log(habilitations[i].join(';'));
}
