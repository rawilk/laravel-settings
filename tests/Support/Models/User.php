<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Models\HasSettings;
use Rawilk\Settings\Tests\Support\database\factories\UserFactory;

class User extends Model
{
    use HasSettings;
    use HasFactory;

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
