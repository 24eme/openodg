var fs = require('fs');
var fileHTML = process.argv[2];
const cheerio = require('cheerio');
const $ = cheerio.load(fs.readFileSync(fileHTML));

var cleanChamps = function(value) {
    value = value.replace(/\n/g, "");
    value = value.replace(/\r/g, "");
    value = value.replace(/[ ]+/g, " ");
    value = value.replace(/^ /, "");
    value = value.replace(/ $/, "");
    value = value.replace(/^NULL$/, "");
    return value;
}

var aoc = cleanChamps($('#ContentPlaceHolder1_ddlAOC option:selected').text());

$('table#ContentPlaceHolder1_gvCepage tr').each(function() {

  var cepage = cleanChamps($(this).find('td').eq(0).text());
  if(!cepage) {
    return;
  }
  console.log(aoc+";"+cepage);
});


