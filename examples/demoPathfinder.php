<?php

/**
 *  Include the API PHP file
 */
require_once '../lib/HTTPUtil.class.php';
require_once '../lib/HttpException.class.php';
require_once '../lib/NotFoundException.class.php';
require_once '../lib/PropertyContainer.class.php';
require_once '../lib/Node.class.php';
require_once '../lib/Relationship.class.php';
require_once '../lib/GraphDatabaseService.class.php';
require_once '../lib/IndexService.class.php';
require_once '../lib/Direction.class.php';

/**
 *  Create a graphDb connection 
 *  Note: this does not actually perform any network access, 
 *      the server is only accessed when you use the database
 */
$graphDb = new GraphDatabaseService('http://localhost:7474/db/data/');

/**
 *  Try finding paths.
 */
$firstNode = $graphDb->getNodeById(1);
$secondNode = $graphDb->getNodeById(3);
$thirdNode = $graphDb->getNodeById(4);

$paths = $firstNode->findPaths($secondNode);
print_r($paths[0]->nodes());
echo "\n";


// What happens when a path doesn't exist?
try {
  $paths = $firstNode->findPaths($thirdNode);
  print_r($paths[0]->nodes());
  echo "\n";
}
catch (NotFoundException $e) {
  echo "Path wasn't found";
  echo "\n";
}
catch (Exception $e) {
  echo "Caught unexpected Exception $e";
  echo "\n";
}

// Get a node that doesn't exist.
$fourthNode = $graphDb->getNodeById(99999);


exit(0);

