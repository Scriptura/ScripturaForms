// @link http://buzut.fr/2014/11/13/ajouter-bouton-lediteur-wysiwyg-wordpress/
// @link http://stackoverflow.com/questions/24871792/tinymce-api-v4-windowmanager-open-what-widgets-can-i-configure-for-the-body-op

( function() {
  tinymce.PluginManager.add( 'ScripturaButtonForms', function( editor, url ) {

    var titleButtonsWarning = 'Forms';
    editor.addButton( 'ScripturaButtonContactFormAdd', { // Ajoute un bouton à tinyMCE
      text: false,
      icon: false,
      title: titleButtonsWarning,
      image: url + '/Images/pen.svg',
      onclick: function() {
            editor.insertContent( // On insère le contenu à l'endroit du curseur
              '[_contact-form]'
            );
      }
    } );

  } );
} )();

