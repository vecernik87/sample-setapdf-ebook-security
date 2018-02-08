<?php

//your variables
$encryption = 'rc40';
$permissions = 0;
$ownerPassword = 'c2ac8e7a78';
$userPassword = '';
$encryptMetadata = false;
$filename = '../pdf/test-word.pdf';


// or if you use composer
require_once('../vendor/autoload.php');


$writer = new SetaPDF_Core_Writer_HttpStream('generated.pdf',true);


//$document = SetaPDF_Core_Document::loadByFilename($filename);
$document = SetaPDF_Core_Document::loadByFilename($filename,$writer);
//$document2 = new SetaPdf_Core_Document($writer);
$pages = $document->getCatalog()->getPages();
//$newPages = $document2->getCatalog()->getPages();
//$firstpage->flattenInheritedAttributes();
// create security page and prepend it
$securitypage = $pages->create(SetaPDF_Core_PageFormats::A4,SetaPDF_Core_PageFormats::ORIENTATION_LANDSCAPE,false);
$pages->prepend($securitypage);

// prepend first page again
// move first page on on begining again
//$firstpage = $pages->getPage(2); // its second because we already prepended security page
$firstpage = $pages->extract(2,$document); // its second because we already prepended security page
$firstpage->flattenInheritedAttributes();
$pages->deletePage(2); // its third because we prepended security and firt page
$pages->prepend($firstpage);



switch($encryption){
    case 'rc40':
        $secHandler = SetaPDF_Core_SecHandler_Standard_Arcfour40::factory($document,$ownerPassword,$userPassword,$permissions);
        break;

    case 'rc128':
        $secHandler = SetaPDF_Core_SecHandler_Standard_Arcfour128::factory($document,$ownerPassword,$userPassword,$permissions);
        break;

    case 'rc128cf':
        $secHandler = SetaPDF_Core_SecHandler_Standard_Arcfour128Cf::factory($document,$ownerPassword,$userPassword,$permissions,$encryptMetadata);
        break;

    case 'aes128':
        $secHandler = SetaPDF_Core_SecHandler_Standard_Aes128::factory($document,$ownerPassword,$userPassword,$permissions,$encryptMetadata);
        break;

    case 'aes256':
        $secHandler = SetaPDF_Core_SecHandler_Standard_Aes256::factory($document,$ownerPassword,$userPassword,$permissions,$encryptMetadata);
        break;
}

$document->setSecHandler($secHandler);

$document->save()->finish();

/*echo "The pdf \"".$filename."\" was encrypted.\n"
."The owner password is \"".$ownerPassword."\".\n"
."The user password is \"".$userPassword."\".\n";
*/