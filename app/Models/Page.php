<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'page_name',
        'company_id',
    ];

    /**
     * The users that belong to the page.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the company that owns the page.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
