const puppeteer = require('puppeteer');
const fs = require('fs');
const readline = require('readline');

if(!process.env.URLSITE){
  throw "Initialisez la variable d'environnement URLSITE";
}

const baseURL = process.env.URLSITE;
exports.baseURL = baseURL;
var browser;

var stdin_lines;
try {
process.stdin.on("data", data => {
    stdin_lines = data.toString().toUpperCase()
})
}catch(e){
  stdin_lines = false;
}

if(process.env.DEBUG){
    console.log('begin');
}

(async () => {
  try {
  if (!process.env.DEBUG && (process.env.DEBUG_WITH_BROWSER != undefined)) {
    process.env.DEBUG = 1;
  }

  browser = await puppeteer.launch(
    {
      headless: !(process.env.DEBUG_WITH_BROWSER),  //mettre à false pour debug
      defaultViewport: {width: 1400, height: 900},
      ignoreDefaultArgs: ['--disable-extensions'],
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    }
    );

    if(!process.env.USER){
      await browser.close();
      throw "Initialisez la variable d'environnement USER avec le login";
    }

    if(!process.env.PASSWORD){
      await browser.close();
      throw "Initialisez la variable d'environnement PASSWORD avec le mot de passe";
    }
    if(!process.env.DOSSIER){
      await browser.close();
      throw "Initialisez la variable d'environnement DOSSIER avec le nom du dossier dans imports";
    }
    if(process.env.DEBUG){
      console.log("===================");
    }
    const page = await browser.newPage();

    await page.goto(baseURL);

    await page.click('#TextBox1');
    await page.waitForSelector('#TextBox1');

    if(process.env.DEBUG){
      console.log("Login page: OK");
      console.log("===================");
    }

    await page.type('#TextBox1', process.env.USER);
    await page.type('#TextBox2', process.env.PASSWORD);

    await page.click('#Button2');

    if(process.env.DEBUG){
      console.log("CONNEXION: OK");
      console.log("===================");
    }

    await page.goto(baseURL+"/operateur/ListeOperateurR.aspx");

    if (!stdin_lines) {

      await page.click("#btnRech");
      await page.waitForSelector('#gvOP');

      client = await page.target().createCDPSession()
      await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: ".",
      });
      await page.click("#Button2");

      xls_filename = '';
      await page.waitForResponse((response) => {
        if (response.status() === 200) {
            xls_filename = response.headers()['content-disposition'];
            xls_filename = xls_filename.replace('attachment; filename=', '');
            if(process.env.DEBUG){
                console.log('xls_filename: ' + xls_filename);
            }
            if (xls_filename.match('xls')) {
                return true;
            }
        }
        return false;
      });
      await page.waitForTimeout(1000);
      await fs.rename(xls_filename, process.env.DOSSIER+'/operateurs.xlsx', (err) => {if (err) return 'ERR';});
      if(process.env.DEBUG){
          console.log('xls saved: ' + process.env.DOSSIER+'operateurs.xlsx (' + xls_filename + ')');
      }
      await browser.close();
      if(process.env.DEBUG){
          console.log('browser closed');
      }
      process.exit(0);
      return ;
    }

    if(process.env.DEBUG){
        console.log('stdin_lines: ');
        console.log(stdin_lines);
    }
    lines = stdin_lines.split('\n');
    for (i in lines) {
      if (!lines[i]) {
        continue;
      }
      console.log(lines[i]);
      args = lines[i].split(";");
      rs = args[1];
      nb = args[0];
      console.log("search for " + rs + "(" + nb + ")");
      await page.$eval('#tbEnt', el => el.value = '');
      await page.type("#tbEnt", rs);
      await page.click("#btnRech");
      let finded = false;
      try {
        await page.waitForSelector("input.icon_modif", {timeout: 1000});
        finded = true;
      } catch (error) {
      }

      if(!finded) {
        await page.goto(baseURL+"/operateur/ListeOpCessation.aspx");
        await page.type("#tbEnt", rs);
        await page.click("#btnRecherche");
        try {
          await page.waitForSelector("input.icon_modif", {timeout: 1000});
          finded = true;
        } catch (error) {
        }
      }

      if(finded) {
        console.log("finded "+rs);
        await page.click("input.icon_modif");

        let newPagePromise = new Promise(x => page.once('popup', x));
        let newPage = await newPagePromise;           // declare new tab /window,

        await page.waitForTimeout(1500);

        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_identite.html",await newPage.content());

        await newPage.goto(baseURL+"/operateur/Commentaire.aspx");
        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_commentaires.html",await newPage.content());

        await newPage.goto(baseURL+"/operateur/LstContact.aspx");
        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_contact.html",await newPage.content());

        await newPage.close();

      }
      console.log(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_*.html saved (" + rs + ")");
    }

    await browser.close();
    process.exit(0);

}catch (e) {
    console.log("");
    console.log('FAILED !!');
    console.log(e);
    await browser.close();
    process.exit(255);
  }
})();
