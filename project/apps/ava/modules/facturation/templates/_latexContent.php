<?php use_helper('TemplatingFacture'); ?>
<?php use_helper('Display'); ?>
\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=3cm, bottom=0.5cm, left=1cm, right=1cm, headheight=2cm, headsep=0mm, marginparwidth=0cm]{geometry}
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
\usepackage{multirow}
\usepackage[framemethod=tikz]{mdframed}

\definecolor{vertclair}{rgb}{0.70,0.79,0.32}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}

\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_ava.png}
\def\LOGOCARTEMEMBRE{<?php echo sfConfig::get('sf_web_dir'); ?>/images/pdf/logo_carte_membre.jpg}
\def\TYPEFACTURE{<?php if($facture->isAvoir()): ?>AVOIR<?php else:?>FACTURE<?php endif; ?>}
\def\NUMFACTURE{<?php echo $facture->numero_ava; ?>}
\def\NUMADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\CAMPAGNE{<?php echo ($facture->getCampageTemplate() + 1).""; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}
\def\FACTUREDATE{Colmar, le <?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>}
\def\FACTUREDECLARANTRS{<?php echo wordwrap(escape_string_for_latex($facture->declarant->raison_sociale), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo wordwrap(escape_string_for_latex($facture->declarant->adresse), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTUREDECLARANTCOMMUNE{<?php echo $facture->declarant->commune; ?>}
\def\FACTUREMEMBRERS{<?php echo escape_string_for_latex($facture->declarant->raison_sociale); ?>}
\def\FACTUREMEMBREADRESSE{<?php echo escape_string_for_latex($facture->declarant->adresse); ?>}
\def\FACTUREMEMBRECOMMUNE{<?php echo escape_string_for_latex($facture->declarant->commune); ?>}
\def\FACTURETOTALHT{<?php echo formatFloat($facture->total_ht); ?>}
\def\FACTURETOTALTVA{<?php echo formatFloat($facture->total_taxe); ?>}
\def\FACTURETOTALTTC{<?php echo formatFloat($facture->total_ttc); ?>}

\definecolor{bg-carte-membre}{RGB}{220,220,220}

\newmdenv[tikzsetting={draw=vertclair,dashed,line width=1pt,dash pattern = on 10pt off 3pt,fill=bg-carte-membre,fill opacity=0.7},%
linecolor=white,backgroundcolor=none, outerlinewidth=1pt]{beamerframe}

\newmdenv[tikzsetting={draw=vertclair,dashed,line width=1pt,dash pattern = on 10pt off 3pt},%
linecolor=white,backgroundcolor=white, outerlinewidth=1pt]{beamerframetotal}

\newsavebox\mysavebox
\newenvironment{imgminipage}[1][]{%
   \def\imgcmd{\includegraphics[width=\wd\mysavebox,height=\dimexpr\ht\mysavebox+\dp\mysavebox\relax,#1]{\LOGOCARTEMEMBRE}}%
   \begin{lrbox}{\mysavebox}%
   \begin{minipage}%
}{%
   \end{minipage}
   \end{lrbox}%

   \sbox\mysavebox{\usebox\mysavebox}%
   \mbox{\rlap{\raisebox{-\dp\mysavebox}{\imgcmd}}\usebox\mysavebox}%
}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyhead[L]{\includegraphics[scale=0.5]{\LOGO}}
\fancyhead[R]{
\colorbox{vertclair}{\LARGE{\textbf{\textcolor{vertfonce}{\TYPEFACTURE~N°~\NUMFACTURE}}}} \\
\vspace{5mm}
N° adhérent : \textbf{\NUMADHERENT}
}
\fancyfoot[C]{}

\begin{document}
	\begin{minipage}{0.5\textwidth}
	    \vspace{-1cm}
		\small{
		\EMETTEURLIBELLE \\
		\EMETTEURADRESSE \\
		\EMETTEURCP~\EMETTEURVILLE \\
		\EMETTEURCONTACT \\
		Email : \EMETTEUREMAIL
		}
	\end{minipage}
	\begin{minipage}{0.5\textwidth}
		\begin{flushleft}
		\vspace{1.6cm}
		\hspace{1.8cm}\FACTUREDECLARANTRS \\
		\hspace{1.8cm}\FACTUREDECLARANTADRESSE \\
		\vspace{2mm}
		\hspace{1.8cm}\FACTUREDECLARANTCP~\FACTUREDECLARANTCOMMUNE
		\end{flushleft}
	\end{minipage}
		\begin{flushleft}
		\vspace{3mm}
		\FACTUREDATE
		\end{flushleft}

\begin{center}
\renewcommand{\arraystretch}{1.2}
\arrayrulecolor{vertclair}
\begin{tabular}{|>{\raggedleft}m{1.9cm}|m{8.8cm}|>{\raggedleft}m{1.9cm}|>{\raggedleft}m{1.9cm}|>{\raggedleft}m{2.3cm}|}
  \hline
  Quantité & Libellés & Prix (€) & Sous-Total & Total \rule[-7pt]{0pt}{20pt} \tabularnewline
  \hline
  <?php foreach ($facture->lignes as $ligne): ?>
  & \textbf{<?php echo $ligne->libelle; ?>} \rule[7pt]{0pt}{11pt} & & & \textbf{<?php echo formatFloat($ligne->montant_ht); ?> €} \rule[7pt]{0pt}{11pt} \tabularnewline
  	<?php foreach ($ligne->details as $detail): ?>
  	    \small{\textit{<?php echo formatQuantite($detail->quantite); ?>}} & \small{\textit{<?php echo $detail->libelle; ?>}} & \small{\textit{<?php echo formatFloat($detail->prix_unitaire); ?>}} & \small{\textit{<?php echo formatFloat($detail->montant_ht); ?>}} &  \tabularnewline
  	<?php endforeach; ?>
  <?php endforeach; ?>
  \hline
  \end{tabular}
\\\vspace{6mm}
\begin{tabular}{|>{\centering}p{9cm} >{\raggedleft}p{6.4cm}|>{\raggedleft}p{2.3cm}|}
  \hline
  \multirow{<?php if($detail->taux_tva): ?>4<?php else: ?>2<?php endif; ?>}{*} {\begin{minipage}{6cm}<?php if(!$facture->isAvoir()): ?>Paiement sous 30 jours à réception \newline de facture, net et sans escompte<?php endif; ?>\end{minipage}}  & \textbf{TOTAL HT} \rule[-5pt]{0pt}{18pt} & \textbf{\FACTURETOTALHT~€} \rule[-5pt]{0pt}{18pt} \tabularnewline
  <?php foreach ($facture->lignes as $ligne): ?>
  	<?php foreach ($ligne->details as $detail): ?>
  	<?php if ($detail->taux_tva): ?>
  	    & \textbf{TVA <?php echo formatFloat($detail->taux_tva * 100); ?>\% <?php if(count($facture->lignes) > 1): ?> sur <?php echo strtolower($ligne->libelle); ?><?php endif; ?>} \rule[-5pt]{0pt}{18pt} & \textbf{<?php echo formatFloat($detail->montant_tva); ?> €} \rule[-5pt]{0pt}{18pt} \tabularnewline
  	<?php endif; ?>
  	<?php endforeach; ?>
  <?php endforeach; ?>
  & \textbf{TOTAL TTC} \rule[-5pt]{0pt}{18pt} & \textbf{\FACTURETOTALTTC~€} \rule[-5pt]{0pt}{18pt} \tabularnewline
  \hline
\end{tabular}
\end{center}
\begin{center}
\small{
SIRET : 778 904 599 00033 - APE : 9412 Z - TVA Intracom. : FR 08 778 904 599
}
\end{center}
	\vspace{1mm}
    <?php if(in_array(DRevClient::TYPE_COUCHDB, $facture->getOrigineTypes()->getRawValue())): ?>
    \begin{imgminipage}{0.5\textwidth}
    \begin{beamerframe}
		\begin{center}
			\vspace{3mm}
			\textbf{\underline{\large{\textsc{association des viticulteurs d'alsace}}}} \\
			Maison des Vins d'Alsace - Colmar \\
			\vspace{7mm}
			\textbf{\large{CARTE DE MEMBRE}} \\
			\vspace{1mm}
			\textbf{\large{Année \CAMPAGNE}} \\
		\end{center}
		\vspace{5.9mm}
		\begin{center}
			\FACTUREMEMBRERS \\
			\FACTUREMEMBREADRESSE \\
			\FACTUREMEMBRECOMMUNE \\
			N° adhérent : \NUMADHERENT \\
		\end{center}
		\vspace{1.6mm}
    \end{beamerframe}
    \end{imgminipage}
    <?php else: ?>
    \begin{minipage}{0.5\textwidth}
        \begin{center}
        \vspace{3mm}
        \end{center}
    \end{minipage}
    <?php endif; ?>
    <?php if(!$facture->isAvoir()): ?>
	\begin{minipage}{0.5\textwidth}
		\vspace{1.2cm}
		\begin{center}
			\textsc{Crédit Agricole Colmar Entreprises} \\
			IBAN : FR76 1720 6007 7049 1243 9001 072 \\
			BIC : AGRIFRPP872
		\end{center}
		\vspace{1cm}
		\begin{beamerframetotal}
		    \vspace{1mm}
			\begin{tabularx}{\linewidth}{X c c}
			\rowcolor{vertclair} \multicolumn{3}{c}{\textbf{\textcolor{vertfonce}{\textsc{référence à rappeler lors du réglement}}}} \\
			\textsc{n° facture} & \textsc{n° adhérent} & \textsc{montant ttc} \rule[-7pt]{0pt}{20pt} \\
			\textbf{\NUMFACTURE} & \textbf{\NUMADHERENT} & \textbf{\FACTURETOTALTTC~€} \\
			\end{tabularx}
		    \vspace{1mm}
		\end{beamerframetotal}
	\end{minipage}
    <?php endif; ?>

\end{document}
