const puppeteer = require('puppeteer');
const fs = require('fs');

if(!process.env.URLSITE){
  throw "Initialisez la variable d'environnement URLSITE";
}

const baseURL = process.env.URLSITE;
exports.baseURL = baseURL;
var browser;

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


    for (let i = 0; i < 1050; i++) {
      let nb = i.toString();
      await page.goto(baseURL+"/operateur/ListeOperateurR.aspx?IDENT="+nb);
      await page.type("#tbCodeInterne", nb);
      await page.click("#btnRech");
      let finded = false;
      try {
        await page.waitForSelector("input.icon_modif", {timeout: 1000});
        finded = true;
      } catch (error) {
      }

      if(!finded) {
        await page.goto(baseURL+"/operateur/ListeOpCessation.aspx?IDENT="+nb);
        await page.type("#tbCodeInterne", nb);
        await page.click("#btnRecherche");
        try {
          await page.waitForSelector("input.icon_modif", {timeout: 1000});
          finded = true;
        } catch (error) {
        }
      }

      if(finded) {
        console.log("finded "+nb);
        await page.click("input.icon_modif");

        let newPagePromise = new Promise(x => page.once('popup', x));
        let newPage = await newPagePromise;           // declare new tab /window,

        await page.waitForTimeout(1500);

        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_identite.html",await newPage.content());

        await newPage.goto(baseURL+"/operateur/Commentaire.aspx");
        fs.writeFileSync(process.env.DOSSIER+"/01_operateurs/fiches/"+nb+"_commentaires.html",await newPage.content());
        await newPage.close();
      }
  }


//    await browser.close();

}catch (e) {
    console.log("");
    console.log('FAILED !!');
    console.log(e);
    await browser.close();
    process.exit(255);
  }
})();
