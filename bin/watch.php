<?php
require(__DIR__.'/../bootstrap.php');


use ElBiniou\LivingSource\Recorder\PathListener;


$command = new PathListener();
$command->execute();


