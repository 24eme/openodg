#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",",dtype={'CVI Opérateur': 'str', 'Identifiant': 'str','Appellation': 'str','Campagne': 'str'}, low_memory=False)
drev_cvg = drev
drev_cdr = drev
drev = drev[['Appellation','Campagne','VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']]
drev = drev[drev['Campagne'] == '2020'].fillna(0) 
drev = drev.query("Appellation == 'CVG' or Appellation=='CDR'") 
drev = drev.groupby(['Appellation']).sum() 
total = drev.sum()
drev = drev.reset_index() 


# In[ ]:


drev_cvg = drev_cvg[['Appellation','Produit','Campagne','VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']]
drev_cvg = drev_cvg[drev_cvg['Campagne'] == '2020'].fillna(0) 
drev_cvg = drev_cvg.query("Appellation == 'CVG'")   
drev_cvg = drev_cvg.drop('Campagne',axis=1)

drev_cvg = drev_cvg.groupby(['Appellation','Produit']).sum()
drev_cvg = drev_cvg.reset_index()


drev = drev.append(drev_cvg)


column = drev.pop("Appellation")
column_P = drev.pop("Produit")
drev.insert(0,'Appellation',column)
drev.insert(1,'Produit',column_P)  
drev.reset_index(drop=True).to_csv("../../web/exports/rhone_stats_drev_VCI.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")


# In[ ]:


drev_cdr = drev_cdr[['Appellation','Produit','Campagne','VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']]
drev_cdr = drev_cdr[drev_cdr['Campagne'] == '2020'].fillna(0) 
drev_cdr = drev_cdr.query("Appellation == 'CDR'")   
drev_cdr = drev_cdr.drop('Campagne',axis=1) 

drev_cdr = drev_cdr.groupby(['Appellation','Produit']).sum()
drev_cdr = drev_cdr.reset_index()

drev = drev.append(drev_cdr) 
drev.loc['Total'] = total 

drev.reset_index(drop=True).to_csv("../../web/exports/stats_drev_vci_2020.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

