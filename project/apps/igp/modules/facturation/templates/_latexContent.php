<?php use_helper('TemplatingFacture'); ?>
<?php use_helper('Display'); ?>
\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=1cm, bottom=3cm, left=1cm, right=1cm, headheight=2cm, headsep=0mm, marginparwidth=0cm]{geometry}
\usepackage{fancyhdr}
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
\usepackage{hhline}
\usepackage{longfbox}

\definecolor{noir}{rgb}{0,0,0}
\definecolor{blanc}{rgb}{1,1,1}
\definecolor{verttresclair}{rgb}{0.90,0.90,0.90}
\definecolor{vertclair}{rgb}{0.70,0.70,0.70}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}
\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_<?php echo strtolower($facture->region); ?>.png}
\def\TYPEFACTURE{<?php if($facture->isAvoir()): ?>Avoir<?php else:?>Relevé de Cotisations<?php endif; ?>}
\def\NUMFACTURE{<?php echo $facture->numero_ava; ?>}
\def\NUMADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\CAMPAGNE{<?php echo ($facture->getCampageTemplate() + 1).""; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo $facture->emetteur->adresse; ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}
\def\EMETTEURIBAN{<?php echo (isset(sfConfig::get('app_facture_emetteur')[strtoupper($facture->region)]['iban']))? sfConfig::get('app_facture_emetteur')[strtoupper($facture->region)]['iban'] : "" ?>}
\def\FACTUREDATE{<?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>}
\def\FACTUREDECLARANTRS{<?php echo wordwrap(escape_string_for_latex($facture->declarant->raison_sociale), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCVI{<?php echo $facture->getCvi(); ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo wordwrap(escape_string_for_latex($facture->declarant->adresse), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCP{<?php echo $facture->declarant->code_postal; ?>}
\def\FACTUREDECLARANTCOMMUNE{<?php echo $facture->declarant->commune; ?>}
\def\FACTURETOTALHT{<?php echo formatFloat($facture->total_ht, ','); ?>}
\def\FACTURETOTALTVA{<?php echo formatFloat($facture->total_taxe, ','); ?>}
\def\FACTURETOTALTTC{<?php echo formatFloat($facture->total_ttc, ','); ?>}
\def\SIRET{<?php echo(CompteClient::getInstance()->findByIdentifiant($facture->identifiant)->societe_informations->siret); ?>}

\pagestyle{fancy}
\renewcommand{\headrulewidth}{0cm}
\renewcommand\sfdefault{phv}
\renewcommand{\familydefault}{\sfdefault}
\fancyhead[L]{}
\fancyhead[R]{

}
\cfoot{\small{
	\EMETTEURLIBELLE \\
	\EMETTEURADRESSE~-~\EMETTEURCP~\EMETTEURVILLE \\
	\EMETTEURCONTACT~-~\EMETTEUREMAIL \\
	N°TVA : FR96803741834 \\
	IBAN : \EMETTEURIBAN \\
}}

\begin{document}

\begin{minipage}{0.5\textwidth}
	\begin{center}
	\hspace{-1.2cm}
	\includegraphics[width=4cm]{\LOGO}
	\end{center}
\end{minipage}
\begin{minipage}{0.5\textwidth}
\lfbox[
  border-width=0.05cm,
  border-color=black,
  border-style=solid,
  width=8.9cm,
  padding={0.2cm,0.2cm},
  text-align=center
]{\textbf{\LARGE{\TYPEFACTURE}}}}

\\\vspace{12mm}

\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{|>{\raggedleft}m{1.0cm}|>{\centering}m{2.8cm}|>{\raggedleft}m{1.0cm}|>{\centering}m{2.8cm}|}
\hhline{|-|-|-|-|}
 \cellcolor{verttresclair} \textbf{N° :} & <?php echo $facture->numero_facture; ?> & \cellcolor{verttresclair} \textbf{Date :} & <?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>  \tabularnewline
 \hhline{|-|-|-|-|}
\end{tabular}

\\\vspace{6mm}

\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{|>{\raggedleft}m{1.0cm}|>{\raggedright}m{7.5cm}|}
\hhline{|-|-|}
\cellcolor{verttresclair} \textbf{CVI :} & \hspace{0.3cm} \FACTUREDECLARANTCVI \tabularnewline
\hhline{|-|-|}
\end{tabular}

\\\vspace{2mm}

\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{|m{8.95cm}|}
\hhline{|-|}
\FACTUREDECLARANTRS \tabularnewline
\FACTUREDECLARANTADRESSE \tabularnewline
\FACTUREDECLARANTCP~\FACTUREDECLARANTCOMMUNE \tabularnewline
\hhline{|-|}
\end{tabular}
\end{minipage}

\\\vspace{4mm}

\begin{center}
\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{|m{9.1cm}|>{\raggedleft}m{1.5cm}|>{\raggedleft}m{2.1cm}|>{\raggedleft}m{1.9cm}|>{\raggedleft}m{2.2cm}|}
  \hline
  \rowcolor{verttresclair} \textbf{Désignation} & \multicolumn{1}{c|}{\textbf{Prix~uni.}} & \multicolumn{1}{c|}{\textbf{Quantité}} & \multicolumn{1}{c|}{\textbf{TVA}} & \multicolumn{1}{c|}{\textbf{Total HT}}  \tabularnewline
  \hline
  <?php foreach ($facture->lignes as $ligne): ?>
    <?php if (count($ligne->details) === 1 && !$ligne->details->getFirst()->libelle): ?>
        \textbf{<?php echo str_replace(array("(", ")"), array('\footnotesize{(', ")}"), $ligne->libelle); ?>} \textbf{Total} &
        <?php echo formatFloat($ligne->details[0]->prix_unitaire, ',') ?> € &
        <?php echo formatFloat($ligne->details[0]->quantite, ',') ?> \texttt{<?php echo ($ligne->details[0]->exist('unite') && $ligne->details[0]->unite)? $ligne->details[0]->unite : "~~" ?>} &
        \textbf{<?php echo ($ligne->montant_tva === 0) ? null : formatFloat($ligne->montant_tva, ',')." €"; ?>} &
        \textbf{<?php echo formatFloat($ligne->montant_ht, ','); ?> €}  \tabularnewline
        \hline
        <?php continue ?>
    <?php endif; ?>
    <?php foreach ($ligne->details as $detail): ?>
        <?php if ($detail->exist('quantite') && $detail->quantite === 0) {continue;} ?>
        <?php echo $ligne->libelle; ?> <?php echo $detail->libelle; ?> &
        {<?php echo formatFloat($detail->prix_unitaire, ','); ?> €} &
        {<?php echo formatFloat($detail->quantite, ','); ?> \texttt{<?php if($detail->exist('unite')): ?><?php echo ($detail->unite); ?><?php else: ?>~~~<?php endif; ?>} &
        <?php echo ($detail->taux_tva) ? formatFloat($detail->montant_tva, ',')." €" : null; ?> &
        <?php echo formatFloat($detail->montant_ht, ','); ?> € \tabularnewline
		\hline
    <?php endforeach; ?>
  <?php endforeach; ?>
  \end{tabular}

\\\vspace{6mm}

\end{center}

\begin{minipage}{0.5\textwidth}
<?= escape_string_for_latex(
    ($facture->exist('modalite_paiement')) ? $facture->modalite_paiement : ''
) ?>
\end{minipage}
\begin{minipage}{0.5\textwidth}
\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{m{2.1cm}|>{\raggedleft}m{3.8cm}|>{\raggedleft}m{2.2cm}|}
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{TOTAL HT} & \textbf{\FACTURETOTALHT~€} \tabularnewline
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{TOTAL TVA 20\%}  & \textbf{\FACTURETOTALTVA~€} \tabularnewline
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{NET À PAYER}  & \textbf{\FACTURETOTALTTC~€} \tabularnewline
  \hhline{|~|-|-}
\end{tabular}
\end{minipage}

<?php if ($facture->exist('paiements') && count($facture->paiements)): ?>
\begin{center}
\\\vspace{2cm}
\flushleft \textbf{\large{Encours de règlement}}}
\\\vspace{0.5cm}
\renewcommand{\arraystretch}{1.5}
\begin{tabular}{|m{5cm}|>{\raggedleft}m{8cm}|>{\raggedleft}m{5cm}|}
  \hline
  \rowcolor{verttresclair} \textbf{Date de règlement} & \multicolumn{1}{c|}{\textbf{Type de règlement}} & \multicolumn{1}{c|}{\textbf{Montant}}  \tabularnewline
  \hline
  <?php foreach ($facture->paiements as $paiement): ?>
				<?php echo DateTime::createFromformat("Y-m-d",$paiement->date)->format('d/m/Y'); ?>&
				<?php echo ($paiement->type_reglement)? FactureClient::$types_paiements[$paiement->type_reglement] : ""; ?>&
				<?php echo formatFloat($paiement->montant,',').' €'; ?>
				\tabularnewline
        \hline
		<?php endforeach; ?>
    \multicolumn{2}{|c}{~} & \textbf{Déjà réglé: <?php echo formatFloat($facture->paiements->getPaimentsTotal(),',').' €' ?>}\tabularnewline
		\hline
		\multicolumn{2}{|c}{~} & \textbf{NET A PAYER : <?php echo formatFloat($facture->total_ttc - $facture->paiements->getPaimentsTotal(),',').' €' ?>}\tabularnewline
    \hline
  \end{tabular}
	\begin{minipage}{0.5\textwidth}
\end{minipage}
<?php endif; ?>
\end{center}
\end{document}
