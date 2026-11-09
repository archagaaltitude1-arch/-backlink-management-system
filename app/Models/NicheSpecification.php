<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['specification_name', 'field_type', 'min_value', 'max_value', 'status'])]
class NicheSpecification extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
        ];
    }
}
