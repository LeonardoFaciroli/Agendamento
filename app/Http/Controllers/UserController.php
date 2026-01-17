<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
public function show(int $id)
    {
        abort(410, 'QR Code de presença desabilitado.');
    }
}
