<?php

/**
 * Permet de chercher des articles par mot-clés
 * Les critères doivent avoir au moins 3 caractères
 * Affiche les résultats regroupés par mois
 * Gestion des erreurs et affichages des critères utilisés
 */

require_once './bibli_generale.php';
require_once ('./bibli_gazette.php');

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

$critValides=[];
$msgErr = null;
$resultat = [];
if(isset($_POST['btnRecherche'])){
    $resultat = traitementRechercheL($critValides, $msgErr);
}

affDebutMenuEntete('Recherche');

affFormulaireRechercheL($critValides, $msgErr, $resultat);

affPiedFin();

ob_end_flush();

/**
 * Rechercher des articles correspondant aux critères soumis
 *
 * @param array       &$critValides Tableau des mot-clés valides (>= 3 caractères)
 * @param string|null &$msgErr      Message d'erreur à afficher si aucun critères valide
 *
 * @return array                    Tableau associatif des articles trouvés
 */
function traitementRechercheL(array &$critValides, ?string &$msgErr) : array {
    if(!parametresControle('post', clesObligatoires:['recherche', 'btnRecherche'], clesFacultatives:[])){
        sessionExit();
    }
    $recherche = trim($_POST['recherche']);
    $criteres = preg_split('/\s+/',$recherche);
    $critValides = array_filter($criteres, 'minTroisL');
    if(empty($critValides)){
        $msgErr = "Le ou les critères de recherche ne sont pas valides";
        return [];
    }
    $bd = bdConnect();
    $conditions = [];
    foreach($critValides as $a => $b){
        $crit = mysqli_real_escape_string($bd, $b);
        $conditions[] = "(arTitre LIKE '%$crit%' OR arResume LIKE '%$crit%')";
    }
    $sql = "SELECT arID, arTitre, arResume, arDatePubli FROM article WHERE " . implode(" AND ", $conditions) . " ORDER by arDatePubli DESC";
    $res = bdSendRequest($bd,$sql);
    $tab = [];
    while($T = mysqli_fetch_assoc($res)){
        $tab[]=$T;
    }
    mysqli_free_result($res);
    mysqli_close($bd);
    return $tab;
}

/**
 * Affiche le formulaire de recherche et les résultats
 *
 * @param array       $critValides Critères valides utilisés pour la recherche
 * @param string|null $msgErr      Message d'erreur éventuel
 * @param array       $resultat    Résultats de la recherche
 *
 * @return void
 */
function affFormulaireRechercheL(array $critValides, ?string $msgErr, array $resultat):void{
    $valRecherche = isset($_POST['recherche']) ? trim($_POST['recherche']) : '';
    echo '<section>',
            '<h2>Rechercher des articles</h2>',
            '<p>Les critères de recherche doivent faire au moins 3 caractères pour être prise en compte.</p>';
    if($msgErr !== null){
        echo '<div class="erreur">', $msgErr, '</div>';
    }
    if($valRecherche !== ''){
        if(!empty($critValides)){
            $valide = implode(" ", $critValides);
            echo '<p class="centre" style="color: green;">Critères de recherche utilisés : "' . $valide . '".</p>';
        }
    }
    echo '<div class="centre">',
            '<form method="post" action="', basename($_SERVER['PHP_SELF']),'">',
                '<input type="text" name="recherche" value="'.htmlProtegerSorties($valRecherche).'" required style="width: 425px;">',
                '<input type="submit" name="btnRecherche" value="Rechercher">',
            '</form>',
         '</div>',
    '</section>';
    if($msgErr===null && $valRecherche !== ''){
        if(empty($resultat)){
            echo '<section>',
                    '<h2>Résultats</h2>',
                        '<p>Aucun article ne correspond aux critères soumis.</p>',
                '</section>';
        }
        else{
            affArticlesGroupesParMois($resultat);
        }
    }
}

/**
 * Vérifie si un mot a au moins 3 caractères
 *
 * @param string $c Mot à tester
 *
 * @return bool     true si >= 3 caractères, false sinon
 */
function minTroisL(string $c){
    return mb_strlen($c, encoding: 'UTF-8') >= 3;
}
