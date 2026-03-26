<?php

namespace Parachute\Contracts\Database;

interface ConnectionContract
{
    /**
     * Get the connection name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the connection configuration.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Get the connection instance.
     *
     * @return mixed
     */
    public function getConnection();
}
