<?php

return [
    'enginePath'                =>  __DIR__ . '/../../IcEngine/',
    'author'                    =>  __DIR__ . '/../../Ice/Var/Site/Author.txt',
    'cli'           =>  [
        'defaultControllerAction'   =>  'Deploy',
        'help'                      =>  '--h',
    ],
    'application'   =>  [
        'bootstrap'             =>  __DIR__ . '/../../Ice/Class/Bootstrap/Vipgeo.php',
        'timezone'              =>  'UTC',
        'path'                  =>  __DIR__ . '/../../',
        'controllersDir'        =>  __DIR__ . '/../../Ice/Controllers/',
        'classesDir'            =>  __DIR__ . '/../../Ice/Class/',
        'viewsDir'              =>  __DIR__ . '/../../Ice/View/',
        'cssDir'                =>  __DIR__ . '/../../Ice/Static/css/',
        'jsDir'                 =>  __DIR__ . '/../../Ice/Static/js/',
        'servicesDir'           =>  __DIR__ . '/../../Ice/Service/',
        'helpersDir'            =>  __DIR__ . '/../../Ice/Class/Helper/',
    ],
    'log'                       =>  __DIR__ . '/../../log/',
];