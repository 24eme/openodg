
echo -n > /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | awk -F ';' '{ print $1 ";1.COMPTE;;;;;" $2 ";;" $3 ";" $4 ";;" $5 ";" $6 ";;" $7 ";" $8 ";;;FRANCE;;;;;;;;;;;;"  }' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | awk -F ';' '{ print $1 ";5.COMMUN;;;;;;;;;multi;;;;;;;;;" $9 ";;;" $10 ";;;;;;"  }' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | cut -d ";" -f 1,11 | grep "PM" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Porteurs de mémoires;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | cut -d ";" -f 1,11 | grep "T" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Techniciens du produit;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | cut -d ";" -f 1,11 | grep "UP" | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Usagers du produit;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | cut -d ";" -f 1,12 | grep -Ev ";$" | awk -F ';' '{ print $1 ";7.COMMEN;;;;;;;;;;;;;;;;;;;;;;;" $2 ";;;;"  }' >> /tmp/comptes_degustateurs_work.csv

cat data/import/degustateurs.csv | cut -d ";" -f 1 | sed 's/$/;4.ATTRIB;;;;;;;;;;;;;;;;;;;;;;;Degustateur;;;;/' >> /tmp/comptes_degustateurs_work.csv

cat /tmp/comptes_degustateurs_work.csv | sort | sed -E 's/^([0-9]{5});/0\1;/' | sed -E 's/^([0-9]{4});/00\1;/' | sed -E 's/^([0-9]{3});/000\1;/' | sed -E 's/^([0-9]{2});/0000\1;/' | grep -E '^[0-9]+;' | sort | sed 's/;Monsieur;/;M;/' | sed 's/;Madame;/;MME;/' | sed 's/;Mme;/;MME;/' | sed 's/;Mademoiselle;/;MLLE;/' | sed 's/;Mlle;/;MLLE;/' | sed 's/;Melle;/;MLLE;/' | sed 's/;M ;/;M;/' | sed 's/;M\.;/;M;/' | bash data/import/import_comptes_degustations_correction.sh > /tmp/degustateurs_tmp.csv; cat data/import/comptes.csv /tmp/degustateurs_tmp.csv | sort > /tmp/comptes_avec_degustateurs.csv; cat /tmp/comptes_avec_degustateurs.csv | grep -E "(;Degustateur;)" | cut -d ";" -f 1 | sort | uniq > /tmp/id_degusteurs.csv; join -t ";" /tmp/comptes_avec_degustateurs.csv /tmp/id_degusteurs.csv > /tmp/comptes_degustateurs.csv

cat /tmp/comptes_degustateurs.csv  | grep "CVI." | grep -v "VIRTUAL"  | cut -d ";" -f 1 > /tmp/degustateurs_cvi

join -t ";" -v 1 /tmp/comptes_degustateurs.csv /tmp/degustateurs_cvi | grep -E "(GROUPE|FONCTION)" > /tmp/comptes_degustateurs_attributs.csv

cat /tmp/degustateurs_tmp.csv /tmp/comptes_degustateurs_attributs.csv | sort | uniq > /tmp/degustateurs_tmp_attributs.csv

cat /tmp/comptes_degustateurs.csv | grep "1.COMPTE" | cut -d ";" -f 1,26,27,28,29 | grep -v ";;;;" > /tmp/comptes_degustateurs_dates.csv

join -t ";" -1 1 -2 1 -a 1 /tmp/degustateurs_tmp_attributs.csv /tmp/comptes_degustateurs_dates.csv | sed 's/;FRANCE;;;;;;;;;;;;/;FRANCE;;;;;;/'