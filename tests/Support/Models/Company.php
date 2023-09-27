<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rawilk\Settings\Models\HasSettings;
use Rawilk\Settings\Tests\Support\database\factories\CompanyFactory;

final class Company extends Model
{
    use HasFactory;
    use HasSettings;

    protected static function newFactory(): CompanyFactory
    {
        return new CompanyFactory;
    }
}
