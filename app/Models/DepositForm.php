<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'deposit',
        'bonus',
        'game_id',
        'page_id',
        'payment_method_id',
        'remarks',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'deposit' => 'decimal:2',
        'bonus' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the deposit form.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the page that owns the deposit form.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the user that created the deposit form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the deposit form.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the payment method for the deposit form.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
