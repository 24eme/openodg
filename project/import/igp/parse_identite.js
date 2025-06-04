var fs = require('fs');
var fileHTML = process.argv[2];
const cheerio = require('cheerio');
const $ = cheerio.load(fs.readFileSync(fileHTML));

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
tel = $('#ctl00_ContentPlaceHolder1_tbTel').prop('value');
fax = $('#ctl00_ContentPlaceHolder1_tbFax').prop('value');
portable = $('#ctl00_ContentPlaceHolder1_tbPortable').prop('value');
courriel = $('#ctl00_ContentPlaceHolder1_tbCourriel').prop('value');

habilitations = []

$('#ctl00_ContentPlaceHolder1_gvlstHabOp tr').each( (h_idx, h) => {
  tds = $(h).find('td');
  if (!$(tds[1]).text()) {
    return;
  }
  habilitations.push(['HABILITATION', ent, raisonsociale, nomentreprise, siret, cvi, ppm, accise, tva, adresse, codepostal, ville, tel, fax, portable, courriel, $(tds[1]).text(), $(tds[0]).text(), $(tds[2]).text()]);
});

$('#ctl00_ContentPlaceHolder1_gvSitesStockages tr').each( (stk_idx, s) => {
  tds = $(s).find('td');
  if (!$(tds[1]).text()) {
    return;
  }
  habilitations.push(['CHAIS', ent,
                      $(tds[0]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' '),
                      $(tds[1]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' '),
                      $(tds[2]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' '),
                      $(tds[3]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' '),
                      $(tds[4]).text().trim().replace("\n", ' ').replace(/\s\s*/, ' ')
                    ]);
});

for (i in habilitations) {
  console.log(habilitations[i].join(';'));
}
