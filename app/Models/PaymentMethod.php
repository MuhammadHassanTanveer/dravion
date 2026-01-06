<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method_id',
        'payment_method_name',
        'company_id',
    ];

    /**
     * The users that belong to the payment method.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the redeem forms for the payment method.
     */
    public function redeemForms()
    {
        return $this->hasMany(RedeemForm::class);
    }

    /**
     * Get the company that owns the payment method.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
