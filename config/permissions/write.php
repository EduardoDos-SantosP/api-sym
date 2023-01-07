<?php

//usings
//endusings

use App\Controller\ContabilController;
use App\Controller\UsuarioController;

return [
	1 => fn(ContabilController $c): Closure => $c->index(...),
	2 => fn(UsuarioController $c): Closure => $c->all(...)
];
