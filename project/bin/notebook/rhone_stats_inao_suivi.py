#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",",dtype={'CVI Opérateur': 'str', 'Identifiant': 'str','Appellation': 'str','Campagne': 'str'}, low_memory=False)

drev = drev[['Campagne','Produit','Nom Opérateur','Adresse Opérateur','Code postal Opérateur','Commune Opérateur','CVI Opérateur','Siret Opérateur','VCI Stock précédent','Superficie revendiqué','Volume revendiqué issu du mutage','Volume revendiqué issu du vci','VCI Complément','VCI Substitution','VCI Rafraichi','VCI Destruction','Volume revendiqué issu de la récolte','VCI Stock final']]


# In[ ]:


drev_2020 = drev[drev['Campagne'] == '2020'].fillna(0)
drev_2020['has_stock'] = drev_2020['VCI Stock précédent'] + drev_2020['VCI Stock final']
drev_2020['VCI N-1 revendiqué'] = drev_2020['Volume revendiqué issu du vci'] + drev_2020['VCI Destruction']
drev_2020 = drev_2020[drev_2020['has_stock'] > 0]
#drev_2020 = drev_2020.sort_values(['Produit'])  
drev_2020 = drev_2020.rename(columns={'VCI Stock précédent': 'VCI N-1','Superficie revendiqué':'Surface (L4)','Volume revendiqué issu du mutage': 'Vol AOC (L15)','Volume revendiqué issu du vci':'Vol VCI (L19)', 'VCI Complément': 'Complément','VCI Substitution':'Substitution','VCI Rafraichi':'Rafraichissement','VCI Destruction':'Destruction','Volume revendiqué issu de la récolte':'Vol AOC issu de la récolte'}) 
drev_2020 = drev_2020.drop(['has_stock', 'Campagne','VCI Stock final'],axis=1) 
drev_2020.loc['Total'] = drev_2020[['Complément','VCI N-1','Rafraichissement','Destruction','VCI N-1 revendiqué']].sum()
drev_2020.reset_index().to_csv("../../web/exports/inao_vci_suivi_2020.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")

