#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
import numpy as np

pd.set_option('display.max_columns', None)

drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Siret Opérateur': 'str', 'Code postal Opérateur': 'str'}, low_memory=False)
etablissements = pd.read_csv("../../web/exports/etablissements.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Login': 'str', 'Identifiant etablissement': 'str'}, index_col=False, low_memory=False)
dr = pd.read_csv("../../web/exports/dr.csv", encoding="iso8859_15", delimiter=";",decimal=",", dtype={'Identifiant': 'str', 'Campagne': 'str', 'Valeur': 'float64'}, low_memory=False)
societe = pd.read_csv("../../web/exports/societe.csv", encoding="iso8859_15", delimiter=";", decimal=",", dtype={'Identifiant': 'str', 'Téléphone' :'str', 'Téléphone portable': 'str'}, index_col=False, low_memory=False)


# In[ ]:


campagne = "2020"   

drev = drev.query("Campagne == @campagne");

drev = drev.groupby(['Identifiant','Appellation','CVI Opérateur']).sum()

drev = drev.reset_index(level=['Identifiant', 'Appellation','CVI Opérateur'])


# In[ ]:


dr = dr.query("Campagne == @campagne");

#dr = dr.query("Appellation != 'CDR' and Appellation != 'CVG'")

dr["Bailleur PPM"] = dr['Bailleur PPM'].fillna("")

#dr avec en colonnes les differentes categorie, et en lignes chaque identifiant
dr = pd.pivot_table(dr, values= 'Valeur', index=['Identifiant',"Appellation",'Bailleur PPM','CVI'],columns=['Code'], aggfunc=np.sum)
dr = dr.reset_index(level=['Bailleur PPM','CVI'])


# In[ ]:


#drev_cru = drev.query("Appellation != 'CDR' and Appellation != 'CVG'")

#merge entre les drev et les dr sur l'identifiant
drev_cru_with_dr = pd.merge(drev, dr, how='outer',left_on=["Identifiant",'Appellation'], right_on=["Identifiant","Appellation"],suffixes=("", " societe"))

drev_cru_with_dr['AOCID-E'] = drev_cru_with_dr['Appellation']+'-'+drev_cru_with_dr['Identifiant']
drev_cru_with_dr['AOCIDBAIL-E'] = drev_cru_with_dr['Bailleur PPM']
#drev_cru_with_dr['AOCIDBAIL-E'] = drev_cru_with_dr['AOCIDBAIL-E'].str[:-2]


# In[ ]:


#sous dataframe pour avoir l'id de l'établissement pour un bailleur
id_bail = etablissements[["Login", "PPM"]]
id_bail = id_bail.rename(columns={'Login': "ID_BAIL",'PPM':"BAILLEURE_PPM"})


# In[ ]:


#renommage des colonnes
drev_cru_with_dr['AOC'] = drev_cru_with_dr['Appellation']
drev_cru_with_dr['DREV_SURF'] = drev_cru_with_dr['Superficie revendiqué']
drev_cru_with_dr['DREV_VOL'] = drev_cru_with_dr['Volume revendiqué net total']


#par code 
drev_cru_with_dr['DR_SURF'] = drev_cru_with_dr['04']
drev_cru_with_dr['DR_REC_TOTALE'] = drev_cru_with_dr['05']
drev_cru_with_dr['DR_VF RAISIN'] = drev_cru_with_dr['06']
drev_cru_with_dr['DR_VF MOUT'] = drev_cru_with_dr['07']
drev_cru_with_dr['DR_CAVE COOP'] = drev_cru_with_dr["08"]
drev_cru_with_dr['DR_CAVE PART'] = drev_cru_with_dr['09']
drev_cru_with_dr['DR_VOL VINIF'] = drev_cru_with_dr['10']
drev_cru_with_dr['DR_VOL AOC'] = drev_cru_with_dr['15']
drev_cru_with_dr['DR_VOL UI'] = drev_cru_with_dr['16']

#drev_cru_with_dr['AOC'] = drev_cru_with_dr['Appellation']


#rempli les cases vide par "" pour ensuite faire des conditions sur ces cases
drev_cru_with_dr["AOCIDBAIL-E"] = drev_cru_with_dr['Bailleur PPM'].fillna("")
drev_cru_with_dr["CVI_OPERATEUR"] = drev_cru_with_dr['CVI Opérateur'].fillna("")

#conditions sur les cases drev_surf et drev_vol
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'DREV_SURF'] = np.nan
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'DREV_VOL'] = np.nan

#condition pour avoir le reste des CVI qui proviennent des dr mais qui ne sont pas dans drev
drev_cru_with_dr.loc[drev_cru_with_dr.CVI_OPERATEUR == "" , 'CVI_OPERATEUR'] = drev_cru_with_dr["CVI"]


# In[ ]:



#merge entre etablissements et societe pour avoir l'adresse de la societe
etablissements['Identifiant societe'] = etablissements['Identifiant etablissement'].str[:-2]
etablissements = pd.merge(societe, etablissements, how='inner',left_on="Identifiant", right_on="Identifiant societe",suffixes=("", " etablissement"))

#merge entre le nouveau df d'établissement et le df dr+drev pour avoir les coordonnées
drev_cru_with_dr = pd.merge(drev_cru_with_dr,etablissements, left_on ='CVI_OPERATEUR', right_on = 'CVI',suffixes=("", " etablissement 2"))


#renommage
drev_cru_with_dr['ID'] = drev_cru_with_dr['Identifiant etablissement']
drev_cru_with_dr = drev_cru_with_dr.rename(columns={'Identifiant etablissement': "ID_ETABLISSEMENT"})


# In[ ]:



#merge entre le tableau finale et le tableau des bailleur pour avoir les coordonnées du bailleur
drev_cru_with_dr = pd.merge(drev_cru_with_dr,id_bail, how='left',left_on='AOCIDBAIL-E',right_on="BAILLEURE_PPM")

#met "" dans case vide pour condition
#drev_cru_with_dr['ID_BAIL'] = drev_cru_with_dr['ID_BAIL'].fillna("")


#merge entre tableau complet et etablissement sur id etablissement du bailleur pour avoir coordonnées du bailleur
drev_cru_with_dr = pd.merge(drev_cru_with_dr,etablissements, how='left', left_on='ID_BAIL', right_on='Identifiant societe', suffixes=("", " bailleur"))


#change toutes les coordonnées pour les lignes avec bailleur
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'ID'] = drev_cru_with_dr['AOCIDBAIL-E']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'CVI_OPERATEUR'] = "Bailleur"
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Raison sociale'] = drev_cru_with_dr['Raison sociale bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Adresse'] = drev_cru_with_dr['Adresse bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Adresse 2'] = drev_cru_with_dr['Adresse 2 bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Adresse 3'] = drev_cru_with_dr['Adresse 3 bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Code postal'] = drev_cru_with_dr['Code postal bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E']!= "" , 'Téléphone'] = drev_cru_with_dr['Téléphone bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Téléphone portable'] = drev_cru_with_dr['Téléphone portable bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'Email'] = drev_cru_with_dr['Email bailleur']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] != "" , 'AOCIDBAIL-E'] = drev_cru_with_dr['AOCID-E']+"/"+drev_cru_with_dr['AOCIDBAIL-E']
drev_cru_with_dr.loc[drev_cru_with_dr['AOCIDBAIL-E'] == "" , 'AOCIDBAIL-E'] = drev_cru_with_dr['AOCID-E']

drev_cru_with_dr['ID_BAIL'] = drev_cru_with_dr['ID_BAIL'].fillna('')


# In[ ]:


#première colonne ...
drev_cru_with_dr['AOCID'] = drev_cru_with_dr['Appellation']+'-'+drev_cru_with_dr['Identifiant societe']
drev_cru_with_dr['AOCIDBAIL'] = drev_cru_with_dr['AOCID']
drev_cru_with_dr.loc[drev_cru_with_dr.ID_BAIL != '', 'AOCIDBAIL'] = drev_cru_with_dr['AOCIDBAIL']+'/'+drev_cru_with_dr['ID']
drev_cru_with_dr.loc[drev_cru_with_dr.ID_BAIL == '', 'ID'] = drev_cru_with_dr['ID'].str[:-2]


# In[ ]:


#colonnes à avoir dans le csv final
drev_cru_with_dr = drev_cru_with_dr[['AOCIDBAIL','AOCID','Appellation', 'DREV_SURF' , 'DREV_VOL', 'DR_SURF', 'DR_REC_TOTALE','DR_VF RAISIN','DR_VF MOUT',"DR_CAVE COOP",'DR_CAVE PART','DR_VOL VINIF','DR_VOL AOC','DR_VOL UI','ID','ID_BAIL','CVI_OPERATEUR','Raison sociale','Adresse','Adresse 2','Adresse 3','Code postal','Commune','Téléphone','Téléphone portable','Email']]

drev_cru_with_dr.drop_duplicates(keep='first',inplace=True)

#trie par AOCIDBAIL
drev_cru_with_dr = drev_cru_with_dr.sort_values(by = 'AOCIDBAIL')


# In[ ]:


#remplace les volumes vide par 0 pour faire la somme si deux lignes ont la même societe
drev_cru_with_dr['DREV_SURF'] = drev_cru_with_dr['DREV_SURF'].fillna(0)
drev_cru_with_dr['DREV_VOL'] = drev_cru_with_dr['DREV_VOL'].fillna(0)
drev_cru_with_dr['DR_SURF'] = drev_cru_with_dr['DR_SURF'].fillna(0)
drev_cru_with_dr['DR_REC_TOTALE'] = drev_cru_with_dr['DR_REC_TOTALE'].fillna(0)
drev_cru_with_dr['DR_VF RAISIN'] = drev_cru_with_dr['DR_VF RAISIN'].fillna(0)
drev_cru_with_dr['DR_VF MOUT'] = drev_cru_with_dr['DR_VF MOUT'].fillna(0)
drev_cru_with_dr['DR_CAVE COOP'] = drev_cru_with_dr['DR_CAVE COOP'].fillna(0)
drev_cru_with_dr['DR_CAVE PART'] = drev_cru_with_dr['DR_CAVE PART'].fillna(0)
drev_cru_with_dr['DR_VOL VINIF'] = drev_cru_with_dr['DR_VOL VINIF'].fillna(0)
drev_cru_with_dr['DR_VOL AOC'] = drev_cru_with_dr['DR_VOL AOC'].fillna(0)
drev_cru_with_dr['DR_VOL UI'] = drev_cru_with_dr['DR_VOL UI'].fillna(0)


#groupby 'societe' on prend les infos du première etablissement de la societe
aggregation_functions = {'AOCIDBAIL':'first','AOCID': 'first', 'Appellation': 'first',  'DREV_SURF': 'sum', 'DREV_VOL': 'sum', 'DR_SURF': 'sum', 
                         'DR_REC_TOTALE': 'sum', 'DR_VF RAISIN': 'sum', 'DR_VF MOUT': 'sum', 'DR_CAVE COOP': 'sum',
                         'DR_CAVE PART': 'sum', 'DR_VOL VINIF': 'sum','DR_VOL AOC': 'sum','DR_VOL UI':'sum',
                         'ID': 'first','ID_BAIL':'first','CVI_OPERATEUR':'first','Raison sociale':'first',
                         'Adresse':'first','Adresse 2':'first','Adresse 3':'first','Code postal':'first','Commune':'first',
                         'Téléphone':'first','Téléphone portable':'first','Email':'first'}
drev_cru_with_dr = drev_cru_with_dr.groupby(drev_cru_with_dr['AOCIDBAIL']).aggregate(aggregation_functions)


# In[ ]:


drev_cru_with_dr.to_csv('../../web/exports/facturation_cotisations_cru+cdr+cdrv_'+campagne, encoding="iso8859_15", sep=";", decimal=",", index=False)

