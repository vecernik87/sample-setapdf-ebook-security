<?php

//your variables
$encryption = 'rc40';
$permissions = 0;
$userPassword = '';
$ownerPassword = 'Locked';
$encryptMetadata = false;
$fileIn = '../pdf/test-links.pdf';
$fileOut = 'test-links-processed.pdf';

// or if you use composer
require_once('../vendor/autoload.php');

// create writer - it define how are we going to output the file (disk, download, inline etc..)
$writer = new SetaPDF_Core_Writer_HttpStream($fileOut,true);
// load document and assign writer
$document = SetaPDF_Core_Document::loadByFilename($fileIn,$writer);
// get pages catalog (list of pages)
$pages = $document->getCatalog()->getPages();

// we can add even annoying popups...
/* $action = new SetaPDF_Core_Document_Action_JavaScript('app.setInterval("app.alert(\'send me some bitcoins!\')",5000)');
  //$action = new SetaPDF_Core_Document_Action_Uri('https://www.google.com/');// or redirect somewhere...
  // which runs shortly after document is opened..
  $document->getCatalog()->setOpenAction($action);/* */


/**
 * CREATE SECURITY PAGE
 */
// create blank security page and put it on begining of the document
$securitypage = $pages->create(SetaPDF_Core_PageFormats::A4,SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE,false);
$pages->prepend($securitypage);
// create a font instance
$font = SetaPDF_Core_Font_Standard_HelveticaBold::create($document);
// get the canvas object
$canvas = $securitypage->getCanvas();

// write some stuff on security page
writeText($securitypage,200,200,$font,'google','http://google.com/');
writeText($securitypage,100,100,$font,'seznam','http://seznam.cz');
writeText($securitypage,10,10,$font,'10*10px invisible link in this corner on each page');


writeText($securitypage,100,400,$font,'Email: john.doe@yopmail.com');
writeText($securitypage,100,420,$font,'Buyer: John Doe');







/**
 * MAKE TITLEPAGE FIRST AGAIN!
 * at the moment it is second because we prepended securitypage
 */
// extract object
$titlepage = $pages->extract(2,$document);
// this ensure that links dont get broken
$titlepage->flattenInheritedAttributes();
// remove from original position
$pages->deletePage(2);
// put it on beginning again
$pages->prepend($titlepage);

//foreach all pages and add security link
$pageCount = $pages->count();
for($pageNo = 1; $pageNo <= $pageCount; $pageNo++){
    $page = $pages->getPage($pageNo);
    writeLink($page,[0,0,10,10],'http://simpleshop.cz');
    writeText($page,300,0,$font,'visible link on each page','http://simpleshop.cz');
}

// add passwords, permissions and encryption
$secHandler = SetaPDF_Core_SecHandler_Standard_Arcfour40::factory($document,$ownerPassword,$userPassword,$permissions);
$document->setSecHandler($secHandler);


// output it using predefined writer
$document->save()->finish();

function writeLink($page,$position,$link){
    $annotations = $page->getAnnotations();
    $linkObj = new SetaPDF_Core_Document_Page_Annotation_Link(
            $position,new SetaPDF_Core_Document_Action_Uri($link)
    );
    $annotations = $page->getAnnotations();
    $annotations->add($linkObj);
}

function writeText($page,$x,$y,$font,$text,$link = ''){
    $fontSize = 14;
    $lineHeight = 1.2;
    $color = [0,0,0];
    // https://manuals.setasign.com/setapdf-core-manual/annotations/link-annotation/
    $canvas = $page->getCanvas();
    $canvas->setGraphicStateSync(
            SetaPDF_Core_Canvas::GS_SYNC_CURRENT_TRANSFORMATION_MATRIX |
            SetaPDF_Core_Canvas::GS_SYNC_TEXT
    );
    $textObj = $canvas->text()
            ->begin()
            ->setRenderingMode(SetaPDF_Core_Canvas_Text::RENDERING_MODE_FILL)
            ->setFont($font,$fontSize)
            ->setLeading($fontSize * $lineHeight)
            ->setNonStrokingColor($color)
            ->moveToNextLine($x,$y)
    ;
    $start = $canvas->graphicState()->text()->getBottomUserSpace();
    $textObj->showText($text);
    $end = $canvas->graphicState()->text()->getTopUserSpace();
    $textObj->end();
    if($link){
        $linkObj = new SetaPDF_Core_Document_Page_Annotation_Link(
                array($start->getX(),$start->getY(),$end->getX(),$end->getY()),new SetaPDF_Core_Document_Action_Uri($link)
        );
        $annotations = $page->getAnnotations();
        $annotations->add($linkObj);
    }
}
