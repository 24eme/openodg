<?php use_helper('TemplatingFacture'); ?>
<?php use_helper('Display'); ?>
\documentclass[a4paper, 10pt]{letter}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[top=1cm, bottom=1.5cm, left=1cm, right=1cm, headheight=2cm, headsep=0mm, marginparwidth=0cm]{geometry}
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
\usepackage{enumitem}

\definecolor{noir}{rgb}{0,0,0}
\definecolor{blanc}{rgb}{1,1,1}
\definecolor{verttresclair}{rgb}{0.90,0.90,0.90}
\definecolor{vertclair}{rgb}{0.70,0.70,0.70}
\definecolor{vertfonce}{rgb}{0.17,0.29,0.28}
\definecolor{vertmedium}{rgb}{0.63,0.73,0.22}
<?php if(file_exists(sfConfig::get('sf_web_dir')."/images/logo_".strtolower($facture->region)."_facturation.png")): ?>
  \def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_<?php echo strtolower($facture->region); ?>_facturation.png}
<?php else: ?>
\def\LOGO{<?php echo sfConfig::get('sf_web_dir'); ?>/images/logo_<?php echo strtolower($facture->region); ?>.png}
<?php endif; ?>
\def\TYPEFACTURE{<?php if($facture->isAvoir()): ?>Avoir<?php else:?>Facture<?php endif; ?>}
\def\NUMFACTURE{<?php echo $facture->numero_odg; ?>}
\def\NUMADHERENT{<?php echo $facture->numero_adherent; ?>}
\def\CAMPAGNE{<?php echo ($facture->getCampageTemplate() + 1).""; ?>}
\def\EMETTEURLIBELLE{<?php echo $facture->emetteur->service_facturation; ?>}
\def\EMETTEURADRESSE{<?php echo str_replace(['–', '\\"'], ['\\\\', ''], $facture->emetteur->adresse); ?>}
\def\EMETTEURCP{<?php echo $facture->emetteur->code_postal; ?>}
\def\EMETTEURVILLE{<?php echo $facture->emetteur->ville; ?>}
\def\EMETTEURCONTACT{<?php echo $facture->emetteur->telephone; ?>}
\def\EMETTEUREMAIL{<?php echo $facture->emetteur->email; ?>}
\def\EMETTEURIBAN{<?php echo Organisme::getInstance($facture->region)->getIban()." ( ".Organisme::getInstance($facture->region)->getBic()." )" ?>}
\def\EMETTEURTVAINTRACOM{<?php echo Organisme::getInstance($facture->region)->getNoTvaIntracommunautaire() ?>}
\def\EMETTEURSIRET{<?php echo Organisme::getInstance($facture->region)->getSiret() ?>}
\def\FACTUREDATE{<?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>}
\def\FACTUREDECLARANTRS{<?php echo wordwrap(escape_string_for_latex($facture->declarant->raison_sociale), 35, "\\\\\hspace{1.8cm}"); ?>}
\def\FACTUREDECLARANTCVI{<?php echo $facture->getCvi(); ?>}
\def\FACTUREDECLARANTIDENTIFIANT{<?php echo $facture->identifiant; ?>}
\def\FACTUREDECLARANTADRESSE{<?php echo escape_string_for_latex($facture->declarant->adresse); ?>}
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
    \EMETTEURCONTACT~~Email~:~\EMETTEUREMAIL \\
}}

\begin{document}

\begin{minipage}{0.5\textwidth}
	\vspace{-0.8cm}
	\includegraphics[width=4cm]{\LOGO} \\
	\textbf{\EMETTEURLIBELLE} \\ \\
	\EMETTEURADRESSE \\
	\EMETTEURCP~\EMETTEURVILLE \\ \\
    \small{
    <?php if(Organisme::getInstance($facture->region)->getNoTvaIntracommunautaire()): ?>
	N°~TVA~:~\EMETTEURTVAINTRACOM \\
    <?php endif; ?>
    SIRET~:~\EMETTEURSIRET \\
    <?php if(Organisme::getInstance($facture->region)->getIban()): ?>
    IBAN~:~\EMETTEURIBAN
    <?php endif; ?>
    }
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
 \cellcolor{verttresclair} \textbf{N° :} & \NUMFACTURE & \cellcolor{verttresclair} \textbf{Date :} & <?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>  \tabularnewline
 \hhline{|-|-|-|-|}
\end{tabular}

\\\vspace{6mm}

\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
<?php if($facture->getCvi()): ?>
\begin{tabular}{|>{\raggedleft}m{1.0cm}|>{\centering}m{2.8cm}|>{\raggedleft}m{1.0cm}|>{\centering}m{2.8cm}|}
\hhline{|-|-|-|-|}
\cellcolor{verttresclair} \textbf{ID :} & \FACTUREDECLARANTIDENTIFIANT & \cellcolor{verttresclair} \textbf{CVI :} & \FACTUREDECLARANTCVI \tabularnewline
\hhline{|-|-|-|-|}
<?php else: ?>
\begin{tabular}{|>{\raggedleft}m{1.0cm}|>{\raggedright}m{7.5cm}|}
\hhline{|-|-|}
\cellcolor{verttresclair} \textbf{ID :} & \FACTUREDECLARANTIDENTIFIANT \tabularnewline
\hhline{|-|-|}
<?php endif; ?>
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

\\\vspace{8mm}

<?php
    $displayTva = false;
    foreach ($facture->lignes as $ligne) {
        foreach ($ligne->details as $detail) {
            if ($detail->taux_tva > 0) {
                $displayTva = true;
                break;
            }
        }
    }
?>

\begin{center}
\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
  <?php $i=0; $exoneration = false; foreach ($facture->lignes as $ligne): ?>
    <?php foreach ($ligne->details as $detail): ?>
        <?php if ($detail->exist('quantite') && $detail->quantite === 0) {continue;} ?>
        <?php if ($i % 40  == 0) : ?>
          <?php if ($i): ?>
              \end{tabular}
              \newpage
          <?php endif; ?>
          \begin{tabular}{|m{9.1cm}|>{\raggedleft}m{1.5cm}|>{\raggedleft}m{2.1cm}|
          <?php if ($displayTva): ?>
          >{\raggedleft}m{1.9cm}|>{\raggedleft}m{2.2cm}|
          <?php else: ?>
          >{\raggedleft}m{4.1cm}|
          <?php endif; ?>}
          \hline
          \rowcolor{verttresclair} \textbf{Désignation} & \multicolumn{1}{c|}{\textbf{Prix~uni.}} & \multicolumn{1}{c|}{\textbf{Quantité}}<?php
          echo $displayTva
              ? ' & \multicolumn{1}{c|}{\textbf{TVA}} & \multicolumn{1}{c|}{\textbf{Total HT}}'
              : ' & \multicolumn{1}{c|}{\textbf{Total HT}}';
          ?>

          \tabularnewline
          \hline
        <?php endif; ?>
        <?php echo $ligne->libelle; ?> <?php echo $detail->libelle; ?>
        <?php if ($detail->taux_tva == 0) {
            $exoneration = true;
            echo '\textbf{*} ';
        }
        ?>&
        {<?php echo formatFloat($detail->prix_unitaire, ','); ?> €} &
        {<?php echo formatFloat($detail->quantite, ','); ?> \texttt{<?php if($detail->exist('unite')): ?><?php echo ($detail->unite); ?><?php else: ?>~~~<?php endif; ?>} &
        <?php if ($displayTva):?>
            <?php echo ($detail->taux_tva) ? formatFloat($detail->montant_tva, ',')." €" : null; ?> &
        <?php endif; ?>
        <?php echo formatFloat($detail->montant_ht, ','); ?> € \tabularnewline
		\hline
    <?php if ($i) $i++ ; else $i = 12; endforeach; ?>
  <?php endforeach; ?>
  \end{tabular}

\\\vspace{10mm}

\end{center}

\begin{minipage}{0.5\textwidth}
~
\end{minipage}
\begin{minipage}{0.5\textwidth}
\renewcommand{\arraystretch}{1.5}
\arrayrulecolor{vertclair}
\begin{tabular}{m{2.1cm}|>{\raggedleft}m{3.8cm}|>{\raggedleft}m{2.2cm}|}
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{TOTAL HT} & \textbf{\FACTURETOTALHT~€} \tabularnewline
  <?php if ($displayTva): ?>
      \hhline{|~|-|-}
      & \cellcolor{verttresclair} \textbf{TOTAL TVA 20\%}  & \textbf{\FACTURETOTALTVA~€} \tabularnewline
  <?php endif;?>
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{TOTAL TTC}  & \textbf{\FACTURETOTALTTC~€} \tabularnewline
  \hhline{|~|-|-}
  & \cellcolor{verttresclair} \textbf{SOMME DUE}  & \textbf{<?php echo formatFloat($facture->total_ttc - $facture->montant_paiement, ','); ?>~€} \tabularnewline
  \hhline{|~|-|-}
\end{tabular}
\end{minipage}

\\\vspace{6mm}
<?php if ($facture->exist('message_communication') && $facture->message_communication): ?>
\textit{<?= escape_string_for_latex($facture->message_communication); ?>} \\ \\
<?php endif; ?>
\\\vspace{6mm}
<?php if (count($facture->paiements)): ?>
\textbf{Paiement(s) :} \\
\begin{itemize}
<?php foreach($facture->paiements as $paiement): ?>
\item <?= (isset(FactureClient::$types_paiements[$paiement->type_reglement])) ? FactureClient::$types_paiements[$paiement->type_reglement]. " de ": ""; ?> <?= formatFloat($paiement->montant, ','); ?>~€,
<?php if ($paiement->date): ?>
le <?php $date = new DateTime($paiement->date); echo $date->format('d/m/Y'); ?>
<?php endif; ?>
\textit{<?= ($paiement->commentaire) ? "(".escape_string_for_latex($paiement->commentaire).")" : ''; ?>}
 \\
<?php endforeach; ?>
\end{itemize}
<?php elseif (!$facture->isAvoir() && $facture->exist('modalite_paiement') && $facture->modalite_paiement): ?>
\textbf{Modalités de paiements} \\
<?= escape_string_for_latex($facture->modalite_paiement) ?>
<?php endif; ?>
\begin{itemize}[noitemsep, topsep=0mm, left=-0.25cm..0.2cm]
    \item[-] Nos conditions de vente ne prévoient pas d'escompte pour paiement anticipé
    \item[-] Conditions de règlement : A réception de la facture
    \item[-] En cas de retard de paiement, seront exigibles, conformément à l'article L 441-10 du code de commerce, une indemnité calculée sur la base de trois fois le taux de l'intérêt légal en vigueur ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 euros
\end{itemize}
<?php if (isset($exoneration) && $exoneration === true): ?>
\\ \\
\textbf{ * : Exonération de TVA en vertu du 9° du 4. de l'article 261 du Code général des impôts}
<?php endif ?>

\end{center}
\end{document}
