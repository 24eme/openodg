var fs = require('fs');
var fileHTML = process.argv[2];
const cheerio = require('cheerio');
const $ = cheerio.load(fs.readFileSync(fileHTML));

trs = $('table.fiche-identite-table td')
raisonsociale = $(trs[1]).text().trim();
nomentreprise = $(trs[3]).text().trim();
siret = $(trs[5]).text().trim();
cvi = $(trs[7]).text().trim();
ppm = $(trs[9]).text().trim();
accise = $(trs[11]).text().trim();
tva = $(trs[13]).text().trim();
adresse = $(trs[15]).find('input').prop('value').trim();
codepostal = $(trs[19]).find('input').prop('value').trim();
ville = $(trs[21]).find('span').first().text().trim();

console.log([raisonsociale, nomentreprise, siret, cvi, ppm, accise, tva, adresse, codepostal, ville]);
