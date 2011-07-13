<?php
use ezp\Content\Section;

use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$sectionService = $sc->getRepository()->getSectionService();

$sectionIdentifier = 'content';
$sectionName = "Content section";

$section = new Section();
$section->identifier = $sectionIdentifier;
$section->name = $sectionName;
try
{
    $sectionService->create( $section );
}
catch ( ValidationException $e )
{
    echo "An error occurred while updating the section: {$e->getMessage()}";
    exit;
}


?>
