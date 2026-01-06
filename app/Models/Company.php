<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_name',
    ];

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the pages for the company.
     */
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get the payment methods for the company.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the deposit forms for the company.
     */
    public function depositForms()
    {
        return $this->hasMany(DepositForm::class);
    }

    /**
     * Get the redeem forms for the company.
     */
    public function redeemForms()
    {
        return $this->hasMany(RedeemForm::class);
    }
}
