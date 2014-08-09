<?php
Symfony\Component\Debug\Debug::enable();
$main = include __DIR__."/production.php";
return array_merge($main, [

    'debug' => true 


]);