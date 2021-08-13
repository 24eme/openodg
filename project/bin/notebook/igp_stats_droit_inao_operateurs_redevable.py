#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import sys

pd.set_option('display.max_columns', None)

dossier_igp = "exports_"+sys.argv[1]
igp = sys.argv[1].replace('igp',"")

drev_lots = pd.read_csv("../../web/"+dossier_igp+"/drev_lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)
etablissements = pd.read_csv("../../web/"+dossier_igp+"/etablissements.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Login': 'str', 'Identifiant etablissement': 'str'}, index_col=False, low_memory=False)


# In[ ]:


societe = pd.read_csv("../../web/"+dossier_igp+"/societe.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Téléphone' :'str', 'Téléphone portable': 'str'}, index_col=False, low_memory=False)


# In[ ]:


lots = pd.read_csv("../../web/"+dossier_igp+"/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", index_col=False, low_memory=False)
changement_denomination = pd.read_csv("../../web/"+dossier_igp+"/changement_denomination.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str','Origine Millésime':'str'}, index_col=False, low_memory=False)


# In[ ]:


lots = lots[(lots['Origine'] == "DRev") | (lots['Origine'] == "DRev:Changé") ]
drev_lots = drev_lots[drev_lots["Type"] == "DRev"]
changement_denomination = changement_denomination[(changement_denomination["Type"] == "DRev") | (changement_denomination["Type"] == "DRev:Changé") ]


# In[ ]:


def createCSVByCampagne(dossier_igp,igp,campagne,drev_lots,lots,changement_denomination,etablissements,societe):
    
    drev_lots = drev_lots.query("Campagne == @campagne");

    #print(drev_lots['Lieu'].unique())
    drev_lots = drev_lots.fillna("")    #ne foncrionne pas pour 2020-2021 pk ?
        
    #VOLUME REVENDIQUE  
    drev_lots = drev_lots.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    drev_lots = drev_lots.reset_index()             
    drev_lots = drev_lots[['Identifiant','Appellation','Couleur','Produit','Lieu','Volume']]
    drev_lots['Type'] = "VOLUME REVENDIQUE"
    
    final = drev_lots
        
    #VOLUME EN INSTANCE DE REVENDICATION
        
    lots = lots.query("Campagne == @campagne");
    lots = lots.fillna("")
    lots_ini = lots  
    lots = lots[(lots['Statut de lot'] != "Conforme") & (lots['Statut de lot'] != "Réputé conforme")]
    lots = lots.groupby(['Id Opérateur','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    lots = lots.reset_index()
    lots = pd.merge(lots, drev_lots , how='left', left_on = ["Id Opérateur",'Produit','Lieu'], right_on = ["Identifiant",'Produit','Lieu'],suffixes=("", " lots"))
    lots = lots[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu']]
    lots['Type'] = "VOLUME EN INSTANCE DE REVENDICATION"
    
    final = final.append(lots,sort= True)    
        
    #CHANGEMENT DE DENO & DECLASSEMENT   
   
    changement_denomination =  changement_denomination.fillna("")    
    changement_denomination = changement_denomination.query("Campagne == @campagne")
    changement_denomination_initial = changement_denomination
    
    
    #DECLASSEMENT
    
    changement_denomination_declassement = changement_denomination[changement_denomination['Type de changement'] == "DECLASSEMENT"]
    changement_denomination_declassement = changement_denomination_declassement.fillna("")    
    changement_denomination_declassement = changement_denomination_declassement.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu'])[["Volume changé"]].sum()
    changement_denomination_declassement  = changement_denomination_declassement.reset_index()  
    changement_denomination_declassement = changement_denomination_declassement.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Volume changé':'Volume','Origine Produit':'Produit'})
    changement_denomination_declassement['Type']= 'DECLASSEMENT'
    changement_denomination_declassement = changement_denomination_declassement[['Identifiant','Appellation','Couleur','Produit','Lieu','Volume','Type']]
    
    final = final.append(changement_denomination_declassement,sort= True)
    
    
    #CHANGEMENT DENOMINATION SRC = PRODUIT
      
    changement_deno = changement_denomination[changement_denomination['Type de changement'] == "CHANGEMENT"]
    changement_deno = changement_deno.fillna("")    
    changement_deno = changement_deno.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu','Appellation','Couleur','Lieu','Produit'])[["Volume changé"]].sum()
    changement_deno  = changement_deno.reset_index()
    changement_deno = changement_deno.rename(columns = {'Origine Appellation': 'Appellation','Origine Couleur':'Couleur','Origine Lieu':'Lieu','Volume changé':'Volume','Origine Produit':'Produit','Appellation':'Nv Appellation','Couleur':'Nv Couleur','Lieu':'NV Lieu','Produit':'Nv Produit'})
        
   
    changement_deno['Libelle'] = changement_deno['Produit'] +'en'+ changement_deno['Nv Produit']
      
    
    changement_deno['Type']= 'CHANGEMENT DENOMINATION SRC = PRODUIT'
    changement_deno = changement_deno[['Identifiant','Appellation','Couleur','Produit','Volume','Type','Libelle','Lieu']]
    
    final = final.append(changement_deno,sort= True)
        
    #CHANGEMENT DENOMINATION DEST = PRODUIT
       
    changement_deno_dest = changement_denomination[changement_denomination['Type de changement'] == "CHANGEMENT"]
    changement_deno_dest = changement_deno_dest.fillna("")    
    changement_deno_dest = changement_deno_dest.groupby(['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origine Lieu','Appellation','Couleur','Lieu','Produit'])[["Volume changé"]].sum()
    changement_deno_dest  = changement_deno_dest.reset_index()
    changement_deno_dest = changement_deno_dest.rename(columns = {'Volume changé':'Volume'})
    changement_deno_dest['Libelle'] = str(changement_deno_dest['Origine Produit'])+'en'+ str(changement_deno_dest['Produit'])
    changement_deno_dest['Type']= 'CHANGEMENT DENOMINATION DEST = PRODUIT'
    changement_deno_dest = changement_deno_dest[['Identifiant','Appellation','Couleur','Produit','Volume','Type','Libelle','Lieu']]
    
    
    final = final.append(changement_deno_dest,sort= True)

    #CSV FINAL
    final = final.sort_values(by=['Identifiant','Appellation','Couleur'])
    
    final.reset_index(drop=True).to_csv('../../web/'+dossier_igp+'/stats/igp_stats_droit_inao_operateurs_redevable_'+campagne+".csv", encoding="iso8859_15", sep=";",index=False, decimal=",")
    
    #tableau récapitulatif
    
    type_vol_revendique = "VOLUME REVENDIQUE"
    type_instance_conformite = "VOLUME EN INSTANCE DE CONFORMITE"
    type_changement_deno_src_produit = "CHANGEMENT DENOMINATION SRC = PRODUIT"
    type_changement_deno_dest_produit = "CHANGEMENT DENOMINATION DEST = PRODUIT"
    type_declassement = "DECLASSEMENT"

    
    tab_cal = final.groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()

    tab_cal['type_vol_revendique'] =  final.query("Type == @type_vol_revendique").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()      
    tab_cal['type_instance_conformite'] =  final.query("Type == @type_instance_conformite").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    tab_cal['type_changement_deno_src_produit'] =  final.query("Type == @type_changement_deno_src_produit").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    tab_cal['type_changement_deno_dest_produit'] =  final.query("Type == @type_changement_deno_dest_produit").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    tab_cal['type_declassement'] =  final.query("Type == @type_declassement").groupby(['Identifiant','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()

    tab_cal = tab_cal.fillna(0)
       
    tab_cal['A'] = tab_cal['type_vol_revendique'] - tab_cal['type_instance_conformite']
    tab_cal ['B'] = tab_cal['type_changement_deno_dest_produit'] - tab_cal['type_changement_deno_src_produit'] - tab_cal['type_declassement']
    tab_cal['A-B'] =  tab_cal['A'] + tab_cal ['B']
    tab_cal = tab_cal.reset_index(level=['Identifiant','Appellation','Couleur','Produit','Lieu'])

    tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Lieu','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B']]

    tab_cal = pd.merge(tab_cal,drev_lots,how='left',left_on=["Identifiant",'Appellation','Couleur','Lieu','Produit'],right_on=["Identifiant",'Appellation','Couleur','Lieu','Produit'],suffixes=("", " info-operateur"))    
    tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B']]

    
    # Pour comparer A-B avec la somme des volumes de lots.csv
    
    lots_init = lots_ini.groupby(['Id Opérateur','Appellation','Couleur','Produit','Lieu'])[["Volume"]].sum()
    lots_init = lots_init.reset_index(level=['Id Opérateur','Appellation','Couleur','Produit','Lieu'])
    lots_init = lots_init.rename(columns = {'Id Opérateur':'Identifiant','Volume':'Somme Volume lots.csv'})
    
    tab_cal = pd.merge(tab_cal,lots_init,how='left', left_on=['Identifiant','Appellation','Couleur','Produit','Lieu'], right_on = ['Identifiant','Appellation','Couleur','Produit','Lieu'],suffixes=("", " lots"))
    tab_cal = tab_cal[['Identifiant','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv']]
    
    #info de l'etablissement et de la societe
    
    etablissements = pd.merge(etablissements,societe,how='outer',left_on="Login",right_on='Identifiant',suffixes=('',' societe'))
    tab_cal = pd.merge(tab_cal,etablissements,how='left',left_on=['Identifiant'],right_on=['Identifiant etablissement'],suffixes=(''," etablissement"))    
    tab_cal['Nom de la société'] = tab_cal['Titre']+" "+tab_cal['Raison sociale societe']
    tab_cal = tab_cal[['Identifiant','Nom de la société','Raison sociale',"Adresse societe","Adresse 2 societe",'Adresse 3 societe','Code postal societe','Commune societe', 'Pays', 'Code comptable societe','Téléphone',"Téléphone portable",'Fax societe','Email societe','Appellation','Couleur','Produit','Volume','Lieu','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv']]
    tab_cal = tab_cal.rename(columns = {'Raison sociale': 'Nom etablissement','Nom Opérateur':'Nom societe','Adresse societe':'Adresse','Adresse 2 societe':'Adresse 2','Adresse 3 societe':'Adresse 3','Code postal societe':'Code postal','Email Operateur':'Email','Fax societe':'Fax'})    
    
    tab_cal.reset_index(drop=True).to_csv('../../web/'+dossier_igp+'/stats/igp_stats_droit_inao_operateurs_redevable_A_B_A-B1'+campagne+".csv", encoding="iso8859_15", sep=";",index=False,  decimal=",")
    
    
    incoherent = tab_cal
    incoherent['Difference A-B / Somme Lots.csv'] = round(incoherent['A-B'] - incoherent['Somme Volume lots.csv'],5)
    
    incoherent = incoherent[incoherent['Difference A-B / Somme Lots.csv'] != 0 ]
    
    changement_denomination_initial = changement_denomination_initial[['Identifiant','Origine Appellation','Origine Couleur','Origine Produit','Origin Lot unique Id']]
    
    
    changement_denomination_initial.drop_duplicates(keep = 'first')  
        
    incoherent = pd.merge(incoherent,changement_denomination_initial,how='left',left_on = ['Identifiant','Appellation','Couleur','Produit'], right_on = ['Identifiant','Origine Appellation','Origine Couleur','Origine Produit'], suffixes=('',' plus'))

    incoherent = incoherent[['Identifiant','Appellation','Couleur','Produit','type_vol_revendique','type_instance_conformite','type_changement_deno_dest_produit','type_changement_deno_src_produit','type_declassement','A','B','A-B','Somme Volume lots.csv','Difference A-B / Somme Lots.csv','Origin Lot unique Id']]

    incoherent['url lot'] = "https://"+igp+".igp.vins.24eme.fr/historique/"+incoherent['Identifiant']+'/'+incoherent['Origin Lot unique Id']
    incoherent.reset_index(drop=True).to_csv('../../web/'+dossier_igp+'/stats/incoherent'+campagne+".csv", encoding="iso8859_15", sep=";",index=False,  decimal=",")

       
    
    


# In[ ]:


createCSVByCampagne(dossier_igp,igp,"2019-2020",drev_lots,lots,changement_denomination,etablissements,societe)
#createCSVByCampagne(dossier_igp,igp,"2020-2021",drev_lots,lots,changement_denomination,etablissements,societe) 


# In[ ]:





# In[ ]:




