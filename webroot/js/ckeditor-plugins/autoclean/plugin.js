/*******************************************************************************
Create Date : 15/02/2010
----------------------------------------------------------------------
Plugin name : autoclean
Version : 1.0
Author : Frommelt Yoann
Description : permet de faire un netoyage des balise HTML compatible avec HTML2PDF
********************************************************************************/

//liste des tags autorisé, ce sont ceux géré par la bibliothèque html2pdf, les autres balises sont interdites et seront affichée au lieu d'être interprétées
var arra_tag_autorise=new Array('a','b','big','blockquote','br','cite','code','div','em','font','form',
'h1','h2','h3','h4','h5','h6','hr','i','img','input','li','link','ol','option','p','pre','s','samp','select','small',
'span','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','tr','u','ul');
//la liste des tags qui sont interdit et qui seront supprimés
var arra_tag_interdit=new Array('title','o');


CKEDITOR.plugins.add('autoclean',{
  beforeInit:function(editor){
    addEventOn(editor);
  }
})

function addEventOn(editor) {
  editor.on('paste', function (evt){
    //alert("autoclean");
    // on recupere le contenu du collé
    var html = (evt.data !== undefined && evt.data.dataValue !== undefined) ? evt.data.dataValue:evt.data['html'];
    // on netoi le code qui provien de word
    var cleanhtml = autoClean(html);
    // on effectue le traitement des balises en fonction des liste blanche / noir
    var protectedhtml = protectTag(cleanhtml);
    // on retourne le contenu netoyer a coller
    evt.data['html'] = protectedhtml;
  });
}

function autoClean(html) {
  //alert(html);
   html = html.replace(/<o:p>\s*<\/o:p>/g, '') ;
   html = html.replace(/<o:p>[\s\S]*?<\/o:p>/g, '&nbsp;') ;

   // Remove mso-xxx styles.
   html = html.replace( /\s*mso-[^:]+:[^;"]+;?/gi, '' ) ;

   // Remove margin styles.
   html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*;/gi, '' ) ;
   html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"" ) ;

   html = html.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, '' ) ;
   html = html.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"" ) ;

   html = html.replace( /\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"" ) ;

   html = html.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"" ) ;

   html = html.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" ) ;

   html = html.replace( /\s*tab-stops:[^;"]*;?/gi, '' ) ;
   html = html.replace( /\s*tab-stops:[^"]*/gi, '' ) ;

   // Remove Class attributes
   html = html.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3") ;

   // Remove style, meta and link tags
   html = html.replace( /<STYLE[^>]*>[\s\S]*?<\/STYLE[^>]*>/gi, '' ) ;
   html = html.replace( /<(?:META|LINK)[^>]*>\s*/gi, '' ) ;

   // Remove empty styles.
   html =  html.replace( /\s*style="\s*"/gi, '' ) ;

   html = html.replace( /<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;' ) ;

   html = html.replace( /<SPAN\s*[^>]*><\/SPAN>/gi, '' ) ;

   // Remove Lang attributes
   html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3") ;

   html = html.replace( /<SPAN\s*>([\s\S]*?)<\/SPAN>/gi, '$1' ) ;

   html = html.replace( /<FONT\s*>([\s\S]*?)<\/FONT>/gi, '$1' ) ;

   // Remove XML elements and declarations
   html = html.replace(/<\\?\?xml[^>]*>/gi, '' ) ;

   // Remove w: tags with contents.
   html = html.replace( /<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi, '' ) ;

   // Remove Tags with XML namespace declarations: <o:p><\/o:p>
   html = html.replace(/<\/?\w+:[^>]*>/gi, '' ) ;

   // Remove comments [SF BUG-1481861].
   html = html.replace(/<\!--[\s\S]*?-->/g, '' ) ;

   html = html.replace( /<(U|I|STRIKE)>&nbsp;<\/\1>/g, '&nbsp;' ) ;

   html = html.replace( /<H\d>\s*<\/H\d>/gi, '' ) ;

   // Remove "display:none" tags.
   html = html.replace( /<(\w+)[^>]*\sstyle="[^"]*DISPLAY\s?:\s?none[\s\S]*?<\/\1>/ig, '' ) ;

   // Remove language tags
   html = html.replace( /<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3") ;

   // Remove onmouseover and onmouseout events (from MS Word comments effect)
   html = html.replace( /<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3") ;
   html = html.replace( /<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3") ;
   
   //alert(html);
   return html;
}

//permet de protéger le code en n'acceptant que certainne balise html
function protectTag(html) {
  var stri_tag=arra_tag_autorise.join('|');
  var stri_res=html;

  var reg1=new RegExp("<([^>]*)>", "gi");
  stri_res=stri_res.replace(reg1,'&lt;$1&gt;');//on remplace les < et les > pour chaque balises

  var stri_tag_interdit=arra_tag_interdit.join('|');
  var reg3=new RegExp("&lt;(/?("+stri_tag_interdit+") ?[^&]*)&gt;", "gi");
  stri_res=stri_res.replace(reg3,'');//suppression des tags interdit

 // var reg2=new RegExp("&lt;(/?("+stri_tag+") ?[^&]*)&gt;", "gi");
  var reg2=new RegExp("&lt;(/?("+stri_tag+")( [^&]*)?)&gt;", "gi");
 
  stri_res=stri_res.replace(reg2,'<$1>');//on remet l'accès au tags autorisés

  return stri_res;
}