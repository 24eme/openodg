#!/usr/bin/env python
# coding: utf-8

# In[ ]:


import pandas as pd
drev = pd.read_csv("../../web/exports/drev.csv", encoding="iso8859_15", delimiter=";", decimal=",",dtype={'CVI Opérateur': 'str', 'Identifiant': 'str','Appellation': 'str','Campagne': 'str'}, low_memory=False)

drev = drev[['Appellation','Campagne','VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']]
drev = drev[drev['Campagne'] == '2020'].fillna(0) 
drev = drev.groupby(['Appellation']).sum() 


# In[ ]:


drev = drev.query("Appellation == 'CVG' or Appellation=='CDR'") 
drev.loc['Total'] = drev.sum()
drev.reset_index().to_csv("../../web/exports/stats_drev_VCI.csv", encoding="iso8859_15", sep=";", index=False, decimal=",")
drev


# In[ ]:


#drev_appellation = pd.DataFrame(drev[['']]) 
#drev_appellation['Total'] = drev_appellation.pivot(columns=['Total'], values=['VCI Stock précédent', 'VCI Destruction', 'VCI Complément', 'VCI Substitution', 'VCI Rafraichi', 'VCI Constitué', 'VCI Stock final']).fillna(0)


# In[ ]:





# In[ ]:




