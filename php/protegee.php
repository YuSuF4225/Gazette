<?php

require_once 'bibli_generale.php';
require_once 'bibli_gazette.php';

// bufferisation des sorties
ob_start();

// démarrage ou reprise de la session
session_start();

// Cette page n'est accessible qu'aux utilisateurs authentifiés.
// => si l'utilisateur n'est pas authentifié, on le redirige sur la page index.php.
// Remarque : il serait plus logique de le rediriger vers la page connexion.php si elle était faite !
if (! estAuthentifie()){
    header('Location: ../index.php');
    exit();
}

// affichage de l'entête
affDebutMenuEntete('Accès restreint');


$bd = bdConnect();

$pseudoE = mysqli_real_escape_string($bd, $_SESSION['pseudo']);

$sql = "SELECT *
        FROM utilisateur
        WHERE utPseudo = '$pseudoE'";

$res = bdSendRequest($bd, $sql);

$T = mysqli_fetch_assoc($res); // remarquez l'absence de boucle !

mysqli_free_result($res);
mysqli_close($bd);

$T = htmlProtegerSorties($T);

echo
    '<section>',
        '<h2>Accès restreint aux utilisateurs authentifiés</h2>',
        '<ul>',
            '<li><strong>Pseudo : ', htmlProtegerSorties($_SESSION['pseudo']), '</strong></li>',
            '<li>SID : ', session_id(), '</li>';
foreach($T as $cle => $val){
    echo    '<li>', $cle, ' : ', $val, '</li>';
}
echo    '</ul>',
    '</section>';

// affichage du pied de page
affPiedFin();

// facultatif car fait automatiquement par PHP
ob_end_flush();
