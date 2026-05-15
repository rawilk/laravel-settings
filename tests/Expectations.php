<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

expect()->extend('toBeQueryCount', function () {
    expect(DB::getQueryLog())->toHaveCount($this->value);
});
