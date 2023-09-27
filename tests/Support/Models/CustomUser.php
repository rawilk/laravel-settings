<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests\Support\Models;

final class CustomUser extends User
{
    protected $table = 'users';

    protected function contextArguments(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
