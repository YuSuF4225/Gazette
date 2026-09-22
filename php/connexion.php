<?php

/**
 * Permet à un utilisateur de se connecter
 * Vérifie le pseudo et le mot de passe
 * Redirige vers la page précédente si existante ou vers index.php
 */

require_once './bibli_generale.php';
require_once ('./bibli_gazette.php');

// bufferisation des sorties
ob_start();

//démarrage ou reprise de la session
session_start();

if(!isset($_SESSION['page_retour']) && isset($_SERVER['HTTP_REFERER'])){
    if(basename($_SERVER['HTTP_REFERER']) !== basename($_SERVER['PHP_SELF'])){
        $_SESSION['page_retour'] = $_SERVER['HTTP_REFERER'];
    }
}

if(estAuthentifie()){
    header('Location: ../index.php');
    exit();
}

if(isset($_POST['btnConnexion'])){
    $err = traitementConnexionL();
}
else{
    $err = null;
}

affDebutMenuEntete('Connexion');

affFormulaireConnexionL($err);

affPiedFin();

ob_end_flush();

/**
 * Vérifie les identifiants de connexion et initialise la session
 *
 * @return bool true si échec de l'authentification, false sinon
 */
function traitementConnexionL() : bool {
    if(!parametresControle('post', clesObligatoires:['pseudo', 'passe', 'btnConnexion'], clesFacultatives:[])){
        sessionExit();
    }
    $pseudo = $_POST['pseudo'] = trim($_POST['pseudo']);
    $passe = $_POST['passe'];
    $bd = bdConnect();
    $pseudoT = mysqli_real_escape_string($bd, $pseudo);
    $sql = "SELECT utPseudo, utPasse, utRedacteur FROM utilisateur WHERE utPseudo = '$pseudoT'";
    $res = bdSendRequest($bd,$sql);
    if (mysqli_num_rows($res) == 0) {
        mysqli_free_result($res);
        mysqli_close($bd);
        return true;
    }
    $tab = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    mysqli_close($bd);

    if(!password_verify($passe,$tab['utPasse'])){
        return true;
    }
    $_SESSION['pseudo'] = $tab['utPseudo'];
    $_SESSION['redacteur'] = ((int)$tab['utRedacteur'] === 1);
    if(isset($_SESSION['page_retour'])){
        $url = $_SESSION['page_retour'];
        unset($_SESSION['page_retour']);
        header("Location: $url");
        exit();
    }
    header('Location: ../index.php');
    exit();
}

/**
 * Affiche le formulaire de connexion
 *
 * @param bool|null $err Indique si une erreur d'authentification doit etre affichés
 *
 * @return void
 */
function affFormulaireConnexionL(?bool $err): void {
    if(isset($_POST['btnConnexion'])){
        $values = htmlProtegerSorties($_POST);
    }
    else{
        $values['pseudo']=$values['passe']='';
    }
    echo '<section>',
            '<h2>Connexion</h2>',
            '<p>Pour vous authentifier, remplissez le formulaire ci-dessous.</p>';
    if(is_bool($err)&&$err===true){
        echo '<div class="erreur">Échec d\'authentification.</div>';
    }
    echo
        '<form method="post" action="', basename($_SERVER['PHP_SELF']),'">',
            '<table>';
    affLigneInput('Pseudo :', array('type' => 'text', 'name' => 'pseudo', 'value' => $values['pseudo'], 'required' => ''));
    affLigneInput('Mot de passe :', array('type' => 'password', 'name' => 'passe', 'value' => '', 'required' => ''));
    echo        '<tr>',
                    '<td colspan="2">',
                        '<input type="submit" name="btnConnexion" value="Se connecter"> ',
                        '<input type="reset" value="Annuler">',
                    '</td>',
                '</tr>',
            '</table>',
        '</form>',
        '<p>',
        'Pas encore inscrit ? N\'attendez pas, <a href="./inscription.php">Inscrivez-vous</a> !',
        '</p>',
    '</section>';
}
