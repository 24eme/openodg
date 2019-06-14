# -*- coding: iso-8859-1 -*
import sys, pandas as pd
from sqlalchemy import create_engine
engine = create_engine('sqlite:///'+sys.argv[1], echo=False, encoding='iso-8859-1')

sys.stderr.write("etablissements.csv\n")
csv = pd.read_csv(sys.argv[2]+"/etablissements.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
csv.to_sql('etablissement', con=engine, if_exists='replace')

sys.stderr.write("chais.csv\n")
csv = pd.read_csv(sys.argv[2]+"/chais.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
csv.to_sql('chai', con=engine, if_exists='replace')

sys.stderr.write("habilitation.csv\n")
csv = pd.read_csv(sys.argv[2]+"/habilitation.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
csv.to_sql('habilitation', con=engine, if_exists='replace')

sys.stderr.write("habilitation_demandes.csv\n")
csv = pd.read_csv(sys.argv[2]+"/habilitation_demandes.csv", encoding='iso-8859-1', delimiter=";", index_col=False)
csv.to_sql('habilitation_demandes', con=engine, if_exists='replace')

sys.stderr.write("drev.csv\n")
csv = pd.read_csv(sys.argv[2]+"/drev.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
csv.to_sql('drev', con=engine, if_exists='replace')

sys.stderr.write("dr.csv\n")
csv = pd.read_csv(sys.argv[2]+"/dr.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
csv.to_sql('dr', con=engine, if_exists='replace')

sys.stderr.write(sys.argv[2]+"/sv12.csv\n")
csv = pd.read_csv(sys.argv[2]+"/sv12.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
csv.to_sql('sv12', con=engine, if_exists='replace')

sys.stderr.write(sys.argv[2]+"/sv11.csv\n")
csv = pd.read_csv(sys.argv[2]+"/sv11.csv", encoding='iso-8859-1', delimiter=";", decimal=",", index_col=False)
csv.to_sql('sv11', con=engine, if_exists='replace')
