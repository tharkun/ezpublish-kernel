#!/usr/bin/env php
<?php

namespace eZ\Publish\Core\Persistence\SqlNg;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/eZ/Publish/API/Repository/Tests/SetupFactory/SqlNg.php';

$setupFactory = new \eZ\Publish\API\Repository\Tests\SetupFactory\SqlNg();

$repository = $setupFactory->getRepository( false );

$persistenceHandlerProperty = new \ReflectionProperty( $repository, 'persistenceHandler' );
$persistenceHandlerProperty->setAccessible( true );
$handler = $persistenceHandlerProperty->getValue( $repository );
$handler->initializeRepository();

