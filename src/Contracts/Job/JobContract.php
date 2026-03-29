<?php

namespace Parachute\Contracts\Job;

interface JobContract
{
    public function resolve(): void;
}
