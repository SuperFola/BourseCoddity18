<?php session_start(); ?>
<!doctype html>
<html>
<?php require "./entete.php"; ?>
    <body>
<?php
// on va utiliser une fonction pour parser le $_GET et savoir quoi afficher
require "./Parsedown.php"; $Parsedown = new Parsedown();  // pour parser du markdown en HTML
require "./UserManager.php"; $UserManager = new UserManager();  // pour avoir accès à la base de données utilisateurs
require "./parseparameters.php";  // pour parser les paramètres GET de la page
require "./generatebreadcrumb.php";  // pour générer le "fil d'Ariane"
// génération de la page web
echo "<div class=\"container-fluid\" id=\"999999\">";
require "./navbar.php";  // inclus et génère la navbar
// inclus et génère les modales
require "./assets/php/messagesModal.php";
require "./assets/php/lireMessageModal.php";
require "./assets/php/nouveauMessageModal.php";
require "./assets/php/erreurModal.php";
require "./config.php";  // contient les variables globales importantes (pour le moment uniquement l'url principale du site web)

if (isset($_GET) && !empty($_GET)) {
    // on parse les paramètres
    $parsed = parseparameters($_GET);
    // et on affiche le fil d'Ariane
    if ($parsed["valid"] == true)
        generateBreadCrumb($parsed);
    else
        generateBreadCrumb(array(), /* focus */ true);
} else {
    // fil d'Ariane par défaut
    generateBreadCrumb(array());
}

if(!isset($parsed) or (isset($parsed["view"]) and $parsed["view"] == "undefined")) {
    require "./assets/php/generatejumbotron.php";
    if (isset($parsed["view"]) and $parsed["view"] == "undefined") {
        // require "./assets/php/generatealertundefined.php";*
    }
} else {
    echo "<div class=\"jumbotron\">";
    if ($parsed["view"] == "undefined") {
        echo $Parsedown->text(file_get_contents("./assets/views/undefined"));
    } elseif ($parsed["view"] == "createprofile") {
        if (isset($_SESSION['error'])) {
            echo $Parsedown->text($_SESSION['error']);
            unset($_SESSION['error']);
        } else {
            // affichage de la vue pour créer son profil
            require "./assets/php/generateformcreateprofile.php";
        }
    } elseif ($parsed["view"] == "editaccount") {
        if (isset($_SESSION['error'])) {
            echo $Parsedown->text($_SESSION['error']);
            unset($_SESSION['error']);
        } else {
            // affichage de la vue pour éditer son profil
            require "./assets/php/generatevieweditaccount.php";
        }
    } elseif ($parsed["view"] == "search") {
        echo $_SESSION["search-head"];
        echo $Parsedown->text($_SESSION['search']);
    } elseif ($parsed["view"] == "viewprofile") {
        if (isset($_SESSION["error"])) {
            echo $Parsedown->text($_SESSION['error']);
            unset($_SESSION['error']);
        } else {
            // affichage du profil demandé
            require "./assets/php/generateviewprofile.php";
        }
    } elseif ($parsed["view"] == "about") {
        echo $Parsedown->text(str_replace("%documentation%", file_get_contents("./documentation/main.md"), file_get_contents("./assets/views/about")));
    } else if ($parsed["view"] == "search-error") {
        echo "Impossible de trouver ce que vous cherchez, une équipe de chimpanzés sur-entrainés a probablement trouvé avant vous ce que vous cherchiez :(";
    } else if ($parsed["view"] == "disconnect") {
        if (isset($_SESSION['error'])) {
            echo $Parsedown->text($_SESSION['error']);
            unset($_SESSION['error']);
        } else {
            // affichage de la vue de déconnexion
            echo $Parsedown->text(file_get_contents("./assets/views/disconnect"));
        }
    } else if ($parsed["view"] == "signin") {
        if (isset($_SESSION['error'])) {
            echo $Parsedown->text($_SESSION['error']);
            unset($_SESSION['error']);
        } else {
            // affichage de la vue pour se connecter
            require "./assets/php/generateformsignin.php";
        }
    } else if ($parsed["view"] == "moderating") {
        if ($UserManager->findUserByPseudo($_SESSION['name']) != null && $UserManager->findUserByPseudo($_SESSION['name'])->is('ADMINISTRATEUR')) {
            require "./assets/php/generatemoderation.php";
        } else {
            echo $Parsedown->text("## Erreur\nVous n'avez pas le droit d'accéder à cette ressource\nPourquoi ne pas retourner à [l'accueil](index.php) ?");
        }
    } else if ($parsed["view"] == "markMessageAsRead") {
        $u = $UserManager->findUserByPseudo($_SESSION['name']);
        $u->markMessageAsRead(intval($parsed["idx"]));
        $UserManager->editUser($u)->updateUsers();
    } else if ($parsed["view"] == "messageModal") {
        // do nothing
    } else {
        echo "<script type=\"text/javascript\">window.location.replace(\"index.php?view=undefined\");</script>";
     }
     echo "</div>";
} ?>
        </div>
<?php require "./assets/php/generatefooter.php"; ?>

        <script type="text/javascript" src="./assets/js/main.js"></script>
        <script type="text/javascript" src="./assets/js/passwordCheckingRegister.js"></script>
        <script type="text/javascript" src="./assets/js/charsCounter.js"></script>
        <script type="text/javascript" src="./assets/js/competencesAdder.js"></script>

        <?php if (isset($parsed) and $parsed["view"] == "editaccount") { ?>
        <script type="text/javascript">
        (function () {
<?php
    $x = $UserManager->findUser($_SESSION['id'])->getCompetences();
    if (!isset($x["empty"])) {
        foreach ($x as $key => $value) {
        ?>
        addCompetence(<?php echo "'" . $key . "', " . $value; ?>);
<?php }} ?>
        })();
        </script>
        <?php } ?>

        <?php if (isset($parsed) && $parsed["view"] == "markMessageAsRead" || $parsed["view"] == "messageModal") { ?>
        <script type="text/javascript">
        (function () {
            $("<?php if (!isset($_SESSION['error'])) { echo "#messagesModal"; } else { echo "#erreurModal"; } ?>").modal('show');
        })();
        </script>
        <?php } ?>
    </body>
</html>
