\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc} 
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=3.5cm, bottom=1.5cm, left=1cm, right=1cm, headheight=4cm, headsep=5mm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
\usepackage{lastpage}
\usepackage{graphicx}
\usepackage[table]{xcolor}
\usepackage{units}
\usepackage{fp}
\usepackage{tikz}
\usepackage{array}
\usepackage{multicol}
\usepackage{textcomp}
\usepackage{marvosym}
\usepackage{lastpage}
\usepackage{truncate}
\usepackage{colortbl} 
\usepackage{tabularx}
\usepackage[style=1]{mdframed}

\definecolor{vertclair}{rgb}{0.70,0.79,0.32}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_site.png}
\def\NUM_FACTURE{<?php echo $facture->numero_facture; ?>}
\def\NUM_ADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\EMETTEUR_LIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEUR_ADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEUR_CP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEUR_VILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEUR_CONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUR_EMAIL{<?php echo $facture->emetteur->email; ?>}
\def\FACTURE_DATE{Colmar, le <?php $date = new DateTime($facture->emetteur->date_emission); echo $date->format('dd/mm/YYYY'); ?>}
\def\FACTURE_DECLARANT_RS{<?php echo $facture->declarant->raison_sociale; ?>}
\def\FACTURE_DECLARANT_ADRESSE{<?php echo $facture->declarant->adresse; ?>}
\def\FACTURE_DECLARANT_CP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTURE_DECLARANT_COMMUNE{<?php echo $facture->declarant->commune; ?>}
\def\FACTURE_TOTAL_HT{<?php echo $facture->total_ht; ?>}
\def\FACTURE_TOTAL_TVA{<?php echo $facture->total_taxe; ?>}
\def\FACTURE_TOTAL_TTC{<?php echo $facture->facture->total_ttc; ?>}

\newmdenv[tikzsetting={draw=vertclair,dashed,line width=1pt,dash pattern = on 10pt off 3pt},%
linecolor=white,backgroundcolor=white, outerlinewidth=1pt]{beamerframe}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyhead[L]{\includegraphics[scale=0.5]{LOGO}}
\fancyhead[R]{
\colorbox{vertclair}{\LARGE{\textbf{\textcolor{vertfonce}{FACTURE}}}} \\ 
\vspace{5mm}
N° facture : \textbf{NUM_FACTURE} \\
N° adhérent : \textbf{NUM_ADHERENT}
}
\fancyfoot[C]{\thepage / \pageref{LastPage}}

\begin{document}
	\begin{minipage}{0.5\textwidth}
		\small{
		EMETTEUR_LIBELLE \\
		EMETTEUR_ADRESSE \\
		EMETTEUR_CP EMETTEUR_VILLE \\
		EMETTEUR_CONTACT \\
		Email : EMETTEUR_EMAIL
		}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\begin{flushright}
		\vspace{-2mm}
		FACTURE_DATE
		\end{flushright}
		\begin{flushleft}
		\vspace{7mm}
		\hspace{1.8cm}FACTURE_DECLARANT_RS \\
		\hspace{1.8cm}FACTURE_DECLARANT_ADRESSE \\
		\vspace{2mm}
		\hspace{1.8cm}FACTURE_DECLARANT_CP FACTURE_DECLARANT_COMMUNE
		\end{flushleft}
	\end{minipage}
	
\vspace{1.5cm}
\begin{center}
\renewcommand{\arraystretch}{1.2}
\arrayrulecolor{vertclair}
\begin{tabular}{r p{13.5cm} c|c}
  <?php foreach ($facture->lignes as $ligne): ?>
  \rowcolor{vertclair} \multicolumn{3}{l}{\textbf{\textcolor{vertfonce}{<?php echo $ligne->libelle; ?>}}} & \textbf{\textcolor{vertfonce}{<?php echo $ligne->montant_ht; ?> €}} \rule[-7pt]{0pt}{20pt} \\
  	<?php foreach ($ligne->details as $detail): ?>
  <?php echo $detail->quantite; ?> & <?php echo $detail->libelle; ?> & <?php echo $detail->prix_unitaire; ?> & <?php echo $detail->montant_ht; ?> \\
  	<?php endforeach; ?>
  <?php endforeach; ?>
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{total}}}} & \textbf{\textcolor{vertfonce}{FACTURE_TOTAL_HT €}} \\
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{tva}}}} & \textbf{\textcolor{vertfonce}{FACTURE_TOTAL_TVA €}} \\
  \rowcolor{vertclair} \multicolumn{3}{r}{\textbf{\textcolor{vertfonce}{\textsc{total ttc à payer}}}} & \textbf{\textcolor{vertfonce}{FACTURE_TOTAL_TTC €}} \\
\end{tabular}	
\end{center}

	\vspace{1.5cm}
	\begin{minipage}{0.5\textwidth}
		\begin{beamerframe}
		\begin{center}
			\textbf{\underline{\large{\textsc{association des viticulteurs d'alsace}}}} \\
			Organisme de Défense et de Gestion des Appellations \\
			\small{Maison des Vins d'Alsace \\
			12 avenue de la Foire aux Vins - B.P. 91225 \\
			68012 COLMAR Cedex \\
			Téléphone 03 89 20 16 50 - Télécopie 03 89 20 16 60 \\
			Email : info@ava-aoc.fr} \\
			\vspace{2mm}
			\textbf{\large{CARTE DE MEMBRE}} \\
			\textbf{\large{Année 2014}} \\
		\end{center}
		\vspace{6mm}
		\begin{tabular}{r l}
			NOM : & EARL WEBER Bernard \\
			Adresse : & 49 rue de Saverne \\
			Commune : & MOLSHEIM \\
			N° adhérent : & 523 \\
		\end{tabular}
		\end{beamerframe}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\vspace{1.4cm}
		\begin{center}
			\textsc{Crédit Agricole Colmar Entreprises} \\
			17206 00770 49124390010 72 \\
			IBAN : FR76 1720 6007 7049 1243 9001 072 \\
			BIC : AGRIFRPP872
		\end{center}
		\vspace{1.4cm}
		\begin{beamerframe}
			\begin{tabularx}{\linewidth}{X c c}
			\rowcolor{vertclair} \multicolumn{3}{c}{\textbf{\textcolor{vertfonce}{\textsc{partie à joindre au règlement}}}} \\
			\textsc{n° facture} & \textsc{n° adhérent} & \textsc{montant ttc} \rule[-7pt]{0pt}{20pt} \\
			\textbf{NUM_FACTURE} & \textbf{NUM_ADHERENT} & \textbf{FACTURE_TOTAL_TTC €} \\
			\end{tabularx}
		\end{beamerframe}
	\end{minipage}

\end{document} 