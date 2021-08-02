#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
pd.set_option('display.max_columns', None)

drev_lots = pd.read_csv("../../web/exports_igpgascogne/drev_lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str', 'Millésime':'str'}, low_memory=False)
lots = pd.read_csv("../../web/exports_igpgascogne/lots.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str'}, index_col=False, low_memory=False)
changement_denomination = pd.read_csv("../../web/exports_igpgascogne/changement_denomination.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Campagne': 'str', 'Millésime':'str','Origine Millésime':'str'}, index_col=False, low_memory=False)


# In[ ]:


drev_lots = drev_lots.rename(columns = {'Date lot': 'Date_lot'})
millesime = "2019"
datemax = "2021"
drev_lots = drev_lots.query("Millésime == @millesime")
drev_lots = drev_lots.query("Date_lot < @datemax")
drev_lots['Lieu'] = drev_lots['Lieu'].fillna('')
drev_lots = drev_lots.groupby(['Appellation','Couleur','Lieu'])[["Volume"]].sum()
drev_lots = drev_lots.reset_index()


# In[ ]:


lots = lots.rename(columns = {'Date lot': 'Date_lot'})
lots = lots.query("Millésime == @millesime")
lots = lots.query("Date_lot < @datemax")

conforme = "Conforme"
rep_conforme = "Réputé conforme"
#en_recours="En recours OC"

lots = lots.rename(columns = {'Statut de lot': 'Statut_de_lot'})
lots = lots.query("Statut_de_lot != @conforme & Statut_de_lot != @rep_conforme");      
# & Statut_de_lot != @en_recours

lots['Lieu'] = lots['Lieu'].fillna('')
lots = lots.groupby(['Appellation','Couleur','Lieu'])[['Volume']].sum()
lots = lots.reset_index()


# In[ ]:


premier_tab = pd.merge(drev_lots, lots , how='left', left_on = ["Appellation",'Couleur','Lieu'], right_on = ["Appellation",'Couleur','Lieu'],suffixes=("", " lots"))

premier_tab['Volume lots'] = premier_tab['Volume lots'].fillna('')
premier_tab = premier_tab.rename(columns = {'Volume': 'Volume revendiqué','Volume lots': 'Volume en instance de conformité'})


# In[ ]:


millesime = "2019"

changement_denomination['Origine Lieu'] = changement_denomination['Origine Lieu'].fillna('')
changement_denomination['Lieu'] = changement_denomination['Lieu'].fillna('')

changement_denomination = changement_denomination.rename(columns = {'Origine Millésime': 'Origine_Millésime','Type de changement':'Type_de_changement'})
changement_denomination = changement_denomination.query("Origine_Millésime == @millesime")

type_de_changement = "DECLASSEMENT"
changement_denomination_declassement = changement_denomination.query("Type_de_changement == @type_de_changement")

changement_denomination_declassement = changement_denomination_declassement.groupby(['Origine Appellation','Origine Couleur','Origine Lieu'])[["Volume changé"]].sum()

changement_denomination_declassement  = changement_denomination_declassement.reset_index()

type_de_changement = "CHANGEMENT"
changement_denomination = changement_denomination.query("Type_de_changement == @type_de_changement")

changement_denomination = changement_denomination.groupby(['Origine Appellation','Origine Couleur','Origine Lieu','Appellation','Couleur','Lieu'])[["Volume changé"]].sum()

changement_denomination = changement_denomination.reset_index()

changement_denomination_changement = changement_denomination

changement_denomination = pd.merge(changement_denomination,changement_denomination_declassement,how='outer',left_on=['Origine Appellation','Origine Couleur','Origine Lieu'],right_on= ['Origine Appellation','Origine Couleur','Origine Lieu'],suffixes=("", " declassement"))

deuxieme_tab = pd.merge(changement_denomination, premier_tab, how = 'right',left_on = ["Origine Appellation",'Origine Couleur','Origine Lieu'], right_on = ["Appellation",'Couleur','Lieu'],suffixes=("", " source"))

deuxieme_tab = deuxieme_tab.rename(columns = {'Appellation': 'Changement déno DEST Appellation', "Couleur":'Changement déno DEST Couleur','Lieu':'Changement déno DEST Lieu'})

deuxieme_tab = deuxieme_tab[['Appellation source','Couleur source','Lieu source','Volume revendiqué','Volume en instance de conformité','Changement déno DEST Appellation','Changement déno DEST Couleur','Changement déno DEST Lieu','Volume changé','Volume changé declassement']]


# In[ ]:


final = pd.merge(deuxieme_tab, changement_denomination_changement, how = 'left',left_on = ["Appellation source",'Couleur source','Lieu source'], right_on = ["Appellation",'Couleur','Lieu'],suffixes=("", " en plus"))

final = final.rename(columns = {'Origine Appellation': 'Changement déno SRC Appellation', "Origine Couleur":'Changement déno SRC Couleur','Origine Lieu': 'Changement déno SRC Lieu','Volume changé en plus':'Volume en plus provenant du changement de déno','Volume changé' :'Volume en moins dû aux changements de déno'})

#colonnes à avoir dans le csv final

final = final[['Appellation source','Couleur source','Lieu source','Volume revendiqué','Volume en instance de conformité','Changement déno DEST Appellation','Changement déno DEST Couleur','Changement déno DEST Lieu','Volume en moins dû aux changements de déno','Changement déno SRC Appellation','Changement déno SRC Couleur', 'Changement déno SRC Lieu', 'Volume en plus provenant du changement de déno','Volume changé declassement']]

#,'Volume changé declassement'

final.reset_index(drop=True).to_csv('../../web/exports/stats_bilan_millessime.csv', encoding="iso8859_15", sep=";",index=False,  decimal=",")

