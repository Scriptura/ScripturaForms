<?php

// -----------------------------------------------------------------------------
// @Plugin Name: Scriptura Forms
// @Plugin URI: https://github.com/Scriptura/ScripturaForms
// @Description: Formulaires
// @Version: 0.0.1
// @Author: Olivier Chavarin
// @Author URI: https://scriptura.github.io/
// @License: ISC
// -----------------------------------------------------------------------------


// @subsection  Languages
// @description Externalisation du plugin
// -----------------------------------------------------------------------------

// @note Fichiers d'externalisation pour les traductions du thème
// @note Name de domaine et emplacement des fichiers de traduction
//load_theme_textdomain( 'scriptura', plugins_url( 'Languages', __FILE__ ) );
load_plugin_textdomain( 'scriptura', false, dirname( plugin_basename( __FILE__ ) ) . '/Languages' );


// -----------------------------------------------------------------------------
// @section     Contact Form
// @description Configuration du formulaire de contact
// -----------------------------------------------------------------------------

if ( ! is_admin() ) {

  function ScripturaContactForm( $mail = '', $uri = '' )
  {
    ob_start();

    global $current_user;
    //$userLogin = $current_user->user_login;
    $userFirstName = $current_user->user_firstname;
    $userLastName = $current_user->user_lastname;
    $userEmail = $current_user->user_email;
    $userDisplayName = $current_user->display_name;

    $uri = '//' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ]; // Alternative à get_permalink()
    $rights = current_user_can( 'read' ); // Droit minimum pour accéder à certaines fonctionnalités
    $emailContacts = get_option( 'scriptura_emails_contact' ); // Emails des correspondants du site
    $fileReady = false;
    $uploadsFolder = '/ScripturaUploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/';
    $uploadsFolderDir = wp_upload_dir()[ 'basedir' ] . $uploadsFolder;
    $uploadsFolderUri = wp_upload_dir()[ 'baseurl' ] . $uploadsFolder;
    $uploadName = $_FILES[ 'userfile' ][ 'name' ];
    //$rand = sprintf( '%04d', mt_rand( 0, 1000 ) ); // @note Fonction aléatoire permettant de distinguer deux fichiers au nom identiques téléchargés à la même seconde
    $rand = ''; // @note Fonction aléatoire court-circtuitée : pas d'intérêt sur un site de fréquentation moyenne, les chances pour qu'un fichier de même nom soit téléchargé à la même seconde étant quasi nulles
    $date = date( 'YmdHis' );
    $uploadType = strtolower(  substr(  strrchr( $uploadName, '.' ), 1 )  ); // Recherche l'extension du fichier
    $uploadCopy = str_replace(
        '.' . $uploadType,
        '',
        $uploadName
      ); // Suppression de l'extention du fichier
    $uploadCopy = str_replace(
      [ '\'', '%20', '.', ',', '-', '_' ],
      [ ' ', ' ', ' ', ' ', ' ', ' ' ],
      $uploadCopy
    ); // Traitement des jeux d'espacement les plus courants
    $uploadCopy = ucwords( $uploadCopy ); // CamelCase
    $uploadCopy = preg_replace( '/[^A-Za-z]/', '', $uploadCopy ); // Traitement des caractères
    $uploadCopy = basename( $uploadCopy ) . $date . $rand . '.' . $uploadType;
    $uploadCopyDir = $uploadsFolderDir . $uploadCopy;
    $uploadCopyUri = $uploadsFolderUri . $uploadCopy;
    $typesReady = [
        'txt', 'md', 'pdf', 'doc', 'docx', 'odt',
        'xls', 'xlsx', 'ods',
        'ppt', 'pptx', 'odp',
        'bmp', 'jpg' , 'jpeg' , 'png', 'gif' , 'tif', 'tiff',
        'mp3', 'm4a', 'ac3', 'wave', 'wma', 'oga', 'aac', '3gp', '3g2',
        'mp4', 'm4v', 'mkv', 'flv', 'wmv', 'avi', 'mov', 'webm', 'ogg', 'ogv', 'divx', 'vob'
      ]; // Liste des types de fichiers autorisés au téléchargement
    $oel = "\n";
    if ( preg_match( '#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#', $mail ) )
      $oel = "\r\n";

    if ( isset( $_POST[ 'submit' ] ) ) : // @note Test permettant d'éviter un conflit avec un autre formulaire placé sur la même page
    if ( ! empty( $_POST ) ) :
    extract( $_POST ); // @note Vérification nom de variable valide
    $valid = true;
    // BEGIN Controllers
    // Controller name:
    if ( strlen( $username ) < 2 ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: your name contains a single character.', 'scriptura' );
    }
    if ( preg_match( '#.{30,}#', $username ) ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: your name is longer than 30 characters.', 'scriptura' );
    }
    if ( ! preg_match( '#^[A-Z]#', $username ) ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: it lacks a capital letter at the beginning of your name.', 'scriptura' );
    }
    if ( preg_match( '#[0-9]#', $username ) ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: numeric characters are placed in your name.', 'scriptura' );
    }
    if ( preg_match( '#[&!?/\+=_;:,$*()<>§@\#\".]#', $username ) ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: special characters or punctuation are placed in your name.', 'scriptura' );
    }
    if ( empty( $username ) ) {
      $valid = false;
      $errorUserName = __( 'Invalid field: your name is not specified.', 'scriptura' );
    }
    // Controller email:
    if ( ! preg_match( '#^[a-z0-9._-]{3,50}@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i', $email ) ) {
      $valid = false;
      $errorEmail = __( 'Invalid field: your email is not consistent.', 'scriptura' );
    }
    if ( empty( $email ) ) {
      $valid = false;
      $errorEmail = __( 'Invalid field: your email is blank.', 'scriptura' );
    }
    // Controller phone:
    if ( ! empty( $phone ) ) {
      if ( ! preg_match( '#^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$#', $phone ) ) {
        $valid = false;
        $errorPhone = __( 'Invalid field: this item does not correspond to a local or international number. Do not put any spaces, dashes or points.', 'scriptura' );
      }
    }
    // Controller country:
    if ( preg_match( '#00#', $country ) ) {
      $valid = false;
      $errorCountry = __( 'You must choose a country.', 'scriptura' );
    }
    // Controller cover:
    if ( ! empty( $test ) ) {
      $valid = false;
    }
    // Controller message:
    if ( ( preg_match( '#<a(.+)<a(.+)<a#', $message ) )
    OR ( preg_match( '#http(.+)http(.+)http#', $message ) ) ) {
      $valid = false;
      $errorMessage = __( 'Invalid field: your message contains more than two links (spam protection).', 'scriptura' );
    }
    if ( strlen( $message ) < 10 ) {
      $valid = false;
      $errorMessage = __( 'Invalid field: your message has less than 10 characters.', 'scriptura' );
    }
    if ( strlen( $message ) > 4000 ) {
      $valid = false;
      $errorMessage = __( 'Invalid field: your message is longer than 4000 characters.', 'scriptura' );
    }
    if ( empty( $message ) ) {
      $valid = false;
      $errorMessage = __( 'Invalid field: you have not completed your message.', 'scriptura' );
    }

    // BEGIN Test
    if ( $rights AND $uploadName ) {
      if ( $_FILES[ 'userfile' ][ 'error' ] > 0 ) {
        $valid = false;
        $errorUserFile = __( 'Error transfer: start again.', 'scriptura' );
      }
      if ( ! in_array( $uploadType, $typesReady ) ) {
        $valid = false;
        $errorUserFile = __( 'Not permitted file extension: this file is not allowed to download.', 'scriptura' );
      }
    }

    if ( $rights ) {
      //if ( $_FILES[ 'userfile' ][ 'error' ] > 0 )
      //  $errorUserFile .= 'Erreur lors du transfert.<br>'; 
      if ( in_array( $uploadType, $typesReady ) ) {
        $fileReady = true;
      }
      if ( $fileReady ) {
        mkdir( $uploadsFolderDir, 0755, true ); // Créer dossier année + sous-dossier mois
      }
    }

    // Controller-error:
    if ( ! $valid ) {
      $errorNotification = '<div id="popin" class="popin">';
      $errorNotification .= '<p class="message-error">' . __( 'An error was detected on the form and it could not be sent. Please check all fields.', 'scriptura' ) . '</p>';
      $errorNotification .= '<a href="#" id="cmd-popin"></a>';
      $errorNotification .= '</div>';
    }
    // END Controllers
    if ( $valid ) :
      $username = htmlentities( addslashes( $username ) );
      $username = stripslashes( $_POST[ 'username' ] );
      $email = htmlentities( addslashes( $email ) );
      $phone = htmlentities( addslashes( $phone ) );
      $message = htmlentities( addslashes( $message ) );
      $message = stripslashes( $_POST[ 'message' ] );
      $to = get_option( 'admin_email' );
      if ( $emailContacts )
        $to = $emailContacts;
      $toCopie = $email;
      $subject = 'Formulaire de contact';
      $subjectCopie = 'Copie de votre message';
      $post = '';
      $post .= __( 'Name:', 'scriptura' ) . $username . '' . $oel;
      $post .= __( 'Email:', 'scriptura' ) . $email . '' . $oel;
      $post .= __( 'Phone:', 'scriptura' ) . $phone . '' . $oel;
      $post .= __( 'Country:', 'scriptura' ) . $country . '' . $oel . '' . $oel;
      $post .= __( 'Message:', 'scriptura' ) . $message;
      $header = 'Reply-To: ' . $to . '' . $oel;
      $headerCopie = 'Reply-To: ' . $email . '' . $oel;
      mail( $to, $subject, $post, $header );
      mail( $toCopie, $subjectCopie, $post, $headerCopie );
      $formPost = '<div><p class="message-success">' . __( 'Thank you Mr (Mrs)', 'scriptura' ) . ' <b>' . $username . '</b>. ' . __( 'Your message has been posted. A copy was sent to the entered email address', 'scriptura' ) . ' (' . $email . ').</p></div>';
      if ( move_uploaded_file( $_FILES[ 'userfile' ][ 'tmp_name' ], $uploadCopyDir ) )
        $formPost .= '<div><p class="message-success">' . __( 'Your attachment has been downloaded from the website:', 'scriptura' ) . ' <a href=' . $uploadCopyUri . '>' . $uploadCopy . '</a></p></div>';
      $formPost .= '<div><form action="' . $uri . '" method="post"><button class="button"><span class="icon-paper-plane"></span>&nbsp;&nbsp;' . __( 'New message', 'scriptura' ) . '</button></form></div>';
      unset( $username );
      unset( $email );
      unset( $phone );
      unset( $country );
      unset( $test );
      unset( $message );
      if ( $rights )
        unset( $userFile ); // @todo En test...
    endif;
    endif;
    endif; // isset($_POST[ 'submit' ] )

    if ( isset( $formPost ) ) {
      echo $formPost;
    } else {
      //echo '<h2 class="emphasized-left">' . __( 'Contact form', 'scriptura' ) . '</h2>';
      echo '<form method="post" action="' . $uri . '"';
      if ( $rights )
        echo ' enctype="multipart/form-data"';
      echo '>';
      echo '<fieldset>';
      if ( isset( $errorNotification ) )
        echo $errorNotification;

      $invalidUserName = '';
      if ( isset( $errorUserName ) )
        $invalidUserName = ' invalid';
      echo '<div class="input' . $invalidUserName . '"><label for="username">' . __( 'Name', 'scriptura' ) . '</label><input type="text" name="username" id="username" value="';
      if ( ! isset( $username ) ) { // Si utilisateur connecté alors identité renseignée
        if ( $userFirstName AND $userLastName ) {
          $username = $userFirstName . ' ' . $userLastName;
        } elseif (  $userFirstName ) {
          $username = $userFirstName;
        } elseif ( $userLastName ) {
          $username = $userLastName;
        } elseif ( $userDisplayName ) {
          $username = $userDisplayName;
        }
      }
      echo stripslashes( $username );
      echo '" size=23 placeholder="' . __( 'John Smith', 'scriptura' ) . '" class="required" required="required">';
      if ( isset( $errorUserName ) )
        echo '<p>' . $errorUserName . '</p>';
      echo '</div>';

      $invalidEmail = '';
      if ( isset( $errorEmail ) )
        $invalidEmail = ' invalid';
      echo '<div class="input' . $invalidEmail . '"><label for="email">' . __( 'Email', 'scriptura' ) . '</label><input type="email" name="email" id="email" value="';
      if ( ! isset( $email ) AND $userEmail ) // Si utilisateur connecté alors email renseigné
        echo $userEmail;
      if ( isset( $email ) )
        echo $email;
      echo '" size=23 placeholder="pseudo@gmail.com" class="required" required="required">';
      if ( isset( $errorEmail ) )
        echo '<p>' . $errorEmail . '</p>';
      echo '</div>';

      $invalidPhone = '';
      if ( isset( $errorPhone ) )
        $invalidPhone = ' invalid';
      echo '<div class="input' . $invalidPhone . '"><label for="phone">' . __( 'Phone', 'scriptura' ) . '</label><input type="phone" name="phone" id="phone" value="';
      if ( isset( $phone ) )
        echo stripslashes( $phone );
      echo '" size=23  placeholder="0158808080">';
      if ( isset( $errorPhone ) )
        echo '<p>' . $errorPhone . '</p>';
      echo '</div>';

      $invalidCountry = '';
      if ( isset( $errorCountry ) )
        $invalidCountry = ' invalid';
      echo '<div class="input' . $invalidCountry . '"><label for="country"><span class="icon-world"></span><span>' . __( 'Country', 'scriptura' ) . '</span></label>';
      echo '<select name="country" id="country" value="';
      if ( isset( $country ) )
        echo $country;
      echo '" class="required">';
      echo '<option value="00" selected="selected">' . __( 'Country', 'scriptura' ) . '</option>'; // Sélection par défaut
      echo '<option value="FR">France</option>'; // Sélection mise en avant
      $uriCsv = plugin_dir_path( __FILE__ ) . 'ListOfCountries.csv'; // Liste des pays ISO 3166-1
      //@note Le chemin doit être définit localement afin d'être compatible avec le SSL.
      // Boucle récupérant la liste de tous les pays
      $id_file = fopen( $uriCsv, 'r' ); // 'r' lecture seule
      while ( $line = fgets( $id_file, 1024 ) ) { // '1024' Nombre d'octets max par ligne et par défaut
        $line = explode( ' ; ', $line ); // Choix du séparateur entre les données de la ligne
        echo '<option value="' . $line[ 0 ] . '">' . $line[ 1 ] . '</option>'; // Retourne toutes les valeurs sur ce format
        if ( $country == $line[0] ) // Sélection en cours
          echo '<option value="' . $line[ 0 ] . '" selected="selected">' . $line[ 1 ] . '</option>'; // Retourne la sélection en cours
      }
      echo '</select>';
      if ( isset( $errorCountry ) )
        echo '<p>' . $errorCountry . '</p>';
      echo '</div>';

      echo '<div class="input hidden"><label for="test">Test</label>';
      echo '<input type="text" name="test" id="test" value="';
      if ( isset( $test ) )
        echo stripslashes( $test );
      echo '" size=23 placeholder="requis"></div>';

      $invalidMessage = '';
      if ( isset( $errorMessage ) )
        $invalidMessage = ' invalid';
      echo '<div class="input' . $invalidMessage . '"><label for="message">' . __( 'Your message', 'scriptura' ) . '</label><textarea name="message" id="message" placeholder="' . __( 'Good Morning...', 'scriptura' ) . '" class="required" required="required">';
      if ( isset( $message ) )
        echo stripslashes( $message );
      echo '</textarea>';
      if ( isset( $errorMessage ) )
        echo '<p>' . $errorMessage . '</p>';
      echo '</div>';

      if ( $rights ) {
        $invalidUserFile = '';
        if ( isset( $errorUserFile ) )
          $invalidUserFile = ' invalid';
        echo '<div class="input' . $invalidUserFile . '"><label for="file">' . __( 'Attached file', 'scriptura' ) . '</label><input type="file" name="userfile" id="file" size=23>'; // Pas de meta value pour ce type d'input
        if ( isset( $errorUserFile ) )
          echo '<p>' . $errorUserFile . '</p>';
        echo '</div>';
      }

      echo '<div><button class="button" name="submit"><span class="icon-paper-plane"></span>&nbsp;&nbsp;' . __('Submit', 'scriptura') . '</button></div>';

      echo '</fieldset>';
      echo '</form>';
    } // isset( $formPost )
    $contactForm = ob_get_clean();
    return $contactForm;
  }
  add_shortcode( '_contact-form', 'ScripturaContactForm' ); // Permet d'appeler le script via un shortcode

} // admin


// -----------------------------------------------------------------------------
// @subsection  TinyMCE
// @description Ajout d'une icône pour TinyMCE v4
// -----------------------------------------------------------------------------

if ( is_admin() ) {

    function ScripturaFormsCmd ()
    {
      global $typenow; // Récupère la variable de contexte du type de post
      if ( ! in_array ( $typenow, [ 'post', 'page' ] ) ) // Activation du plugin pour les articles et les pages
        return;
      add_filter ( 'mce_external_plugins', 'ScripturaAddTinymcePluginForms' ); // Ajout javascript à l'éditeur de WP
      add_filter ( 'mce_buttons_4', 'ScripturaAddTinymceButtonForms' ); // Ajoute un bouton à la première ligne de boutons
    }
    add_action( 'admin_head', 'ScripturaFormsCmd' );

    function ScripturaAddTinymcePluginForms ( $plugin )
    {
      $plugin[ 'ScripturaButtonForms' ] = plugins_url( 'Scripts.js', __FILE__ ); // Emplacement de la fonction des bouttons
      return $plugin;
    }

    function ScripturaAddTinymceButtonForms ( $buttons )
    { // Id du bouton pour faire la correspondance avec le JS
      array_push( $buttons, 'ScripturaButtonContactFormAdd' ); // Passage d'un tableau contenant l'id du bouton, pour ajouter d'autres boutons il suffit de passer les autres id
      return $buttons;
    }

} // admin

