import puppeteer from 'puppeteer';
import fs from 'fs';
import readline from 'readline';
import {setTimeout} from "node:timers/promises";

if(!process.env.URLSITE){
  throw "Initialisez la variable d'environnement URLSITE";
}

const baseURL = process.env.URLSITE;
var browser;

var stdin_lines;
try {
  stdin_lines = fs.readFileSync(process.env.PATHOPERATOR).toString();
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
      await setTimeout(1000);
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

    var lines = stdin_lines.split('\n');
    for (var i in lines) {
      if (!lines[i]) {
        continue;
      }
      console.log(lines[i]);
      var args = lines[i].split(";");
      var rs = args[1];
      var nb = args[0];
      var cvi = args[2].replaceAll(' ', '');
      var siret = args[3].replaceAll(' ', '');

      if (cvi.length != 10) {
        cvi = '';
      }
      if (siret.length != 14) {
	siret = '';
      }

      if (fs.existsSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_contact.html")) {
        console.log("file contact.html exists for " + rs + "(" + nb + ")");
        continue;
      }

      if (!cvi && !siret) {
        console.log(rs + " (" + nb + ") has no cvi or siret");
      }

      await page.goto(baseURL+"/operateur/ListeOperateurR.aspx");
      page.waitForSelector("#tbCVI", {timeout: 1000});

      console.log("search for " + rs + "(" + nb + ")");
      await page.$eval('#tbEnt', el => el.value = '');
      await page.$eval('#tbCVI', el => el.value = '');
      await page.$eval('#tbSiret', el => el.value = '');
      if (!cvi && !siret) {
         await page.type("#tbEnt", rs);
      }else{
         await page.type("#tbCVI", cvi);
         await page.type("#tbSiret", siret);
      }
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

        await setTimeout(1500);

        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_identite.html",await newPage.content());

        await newPage.goto(baseURL+"/operateur/Commentaire.aspx");
        await setTimeout(1500);
        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_commentaires.html",await newPage.content());

        await newPage.goto(baseURL+"/operateur/LstContact.aspx");
        await setTimeout(1500);
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
