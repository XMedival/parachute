<?php

namespace Parachute\Contracts\Support;

use Parachute\Http\Request;
use Parachute\Http\Response;

interface Responsable
{
    public function toResponse(Request $request): Response;
}
