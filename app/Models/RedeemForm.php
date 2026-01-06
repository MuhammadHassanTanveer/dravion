<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'redeem',
        'tip',
        'paid',
        'customer_tag',
        'page_id',
        'status',
        'payment_method_id',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'redeem' => 'decimal:2',
        'tip' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the redeem form.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the page that owns the redeem form.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the user that created the redeem form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method for the redeem form.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the company that owns the redeem form.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
