<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};


class Quotation extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'quotation_number',
        'client_id',
        'quotation_date',
        'expiry_date',
        'notes',
        'sub_total',
        'discount_percent',
        'discount_amount',
        'vat_percent',
        'vat_amount',
        'tax_percent',
        'tax_amount',
        'installation_charge',
        'round_off',
        'total_amount',
        'status',

        // PDF snapshot fields
        'client_name',
        'highest_designation',
        'client_designation',
        'client_address',
        'client_phone',
        'client_email',
        'attention_to',
        'body_content',
        'terms_conditions',
        'subject',
        'company_name',
        'company_phone',
        'company_email',
        'company_website',
        'company_address',
        'logo',
        'signatory_name',
        'signatory_designation',
        'signatory_photo',
        'additional_enclosed',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'installation_charge' => 'decimal:2',
        'round_off' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            // Only auto-generate if quotation_number is not already set
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber($quotation);
            }
        });
    }

    public static function generateQuotationNumber($quotation = null)
    {
        // Get client/company name prefix
        $suffix = 'quo'; // Default fallback

        if ($quotation) {
            // Try to get client name first
            if ($quotation->client_id) {
                $client = Client::find($quotation->client_id);
                if ($client) {
                    // Use first word of client name, lowercase
                    $suffix = strtolower(explode(' ', trim($client->name))[0]);
                }
            }
            // If no client, try company name
            elseif (!empty($quotation->client_name)) {
                $suffix = strtolower(explode(' ', trim($quotation->client_name))[0]);
            }
        }

        // Clean suffix: only allow alphanumeric characters
        $suffix = preg_replace('/[^a-z0-9]/', '', $suffix);
        if (empty($suffix)) {
            $suffix = 'quo';
        }

        // Get the highest sequence number from ALL quotations (global counter)
        $lastQuotation = static::withTrashed() // include deleted quotations
            ->orderBy('id', 'desc')
            ->first();

        // Extract last sequence number
        $sequence = 1;
        if ($lastQuotation) {
            // Get the numeric part from the last quotation number
            $parts = explode('-', $lastQuotation->quotation_number);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                $sequence = (int)$parts[1] + 1;
            } else {
                // If format doesn't match, try to extract number
                preg_match('/(\d+)$/', $lastQuotation->quotation_number, $matches);
                if (!empty($matches)) {
                    $sequence = (int)$matches[1] + 1;
                }
            }
        }

        // Format: abcd-0001, abcd-0002, bcde-0003, etc.
        return "{$suffix}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

}
