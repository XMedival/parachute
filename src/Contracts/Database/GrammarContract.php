<?php

namespace Parachute\Contracts\Database;

use Parachute\Database\Schema\Blueprint;
use Parachute\Database\Schema\Column;

interface GrammarContract
{
    public function compileTableExists(string $table): array;
    public function compileGetTables(): string;
    public function compileColumn(Column $column): string;
    public function compileCreateTable(Blueprint $blueprint): string;
    public function compileDropTable(string $table): string;
    public function compileDropTableIfExists(string $table): string;
    public function compileNow(): string;
}
