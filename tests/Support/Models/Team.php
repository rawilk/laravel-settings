<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Tests\Support\database\factories\TeamFactory;

final class Team extends Model
{
    use HasFactory;

    protected static function newFactory(): TeamFactory
    {
        return new TeamFactory;
    }
}
