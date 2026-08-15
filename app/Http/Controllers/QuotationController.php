<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use App\Models\{Client, CompanyDetail, Product, Quotation, QuotationItem, User};
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = Quotation::with('client')
            ->when($request->filled('quotation_number'), function ($query) use ($request) {
                $query->where('quotation_number', 'like', '%' . trim((string) $request->quotation_number) . '%');
            })
            ->when($request->filled('client_name'), function ($query) use ($request) {
                $clientName = trim((string) $request->client_name);
                $query->whereHas('client', function ($clientQuery) use ($clientName) {
                    $clientQuery->where('name', 'like', '%' . $clientName . '%');
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('quotation_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('quotation_date', '<=', $request->date_to);
            })
            ->latest()
            ->get();

        return view('frontend.pages.quotations.index', compact('quotations'));
    }

    public function export(Request $request)
    {
        $quotations = Quotation::with('client')
            ->when($request->filled('quotation_number'), function ($query) use ($request) {
                $query->where('quotation_number', 'like', '%' . trim((string) $request->quotation_number) . '%');
            })
            ->when($request->filled('client_name'), function ($query) use ($request) {
                $clientName = trim((string) $request->client_name);
                $query->whereHas('client', function ($clientQuery) use ($clientName) {
                    $clientQuery->where('name', 'like', '%' . $clientName . '%');
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('quotation_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('quotation_date', '<=', $request->date_to);
            })
            ->latest()
            ->get();

        $headers = [
            'SL',
            'Quotation No.',
            'Client Name',
            'Client Phone',
            'Client Email',
            'Quotation Date',
            'Sub Total',
            'Discount Amount',
            'VAT Amount',
            'Tax Amount',
            'Installation Charge',
            'Total Amount',
            'Signatory',
        ];

        $rows = [];
        $sl = 1;

        foreach ($quotations as $quotation) {
            $clientName = $quotation->client?->name ?? $quotation->client_name ?? '';
            $clientPhone = $quotation->client?->phone ?? $quotation->client_phone ?? '';
            $clientEmail = $quotation->client?->email ?? $quotation->client_email ?? '';
            $date = optional($quotation->quotation_date)->format('Y-m-d') ?? '';

            $rows[] = [
                $sl++,
                (string)$quotation->quotation_number,
                $clientName,
                (string)$clientPhone,
                $clientEmail,
                $date,
                $quotation->sub_total !== null ? (float)$quotation->sub_total : '',
                $quotation->discount_amount !== null ? (float)$quotation->discount_amount : '',
                $quotation->vat_amount !== null ? (float)$quotation->vat_amount : '',
                $quotation->tax_amount !== null ? (float)$quotation->tax_amount : '',
                $quotation->installation_charge !== null ? (float)$quotation->installation_charge : '',
                $quotation->total_amount !== null ? (float)$quotation->total_amount : '',
                $quotation->signatory_name ?? '',
            ];
        }

        $fileName = 'quotations_' . date('Y-m-d') . '.xlsx';

        return \App\Helpers\SimpleXlsxExporter::download($fileName, $headers, $rows);
    }

    public function create()
    {
        $clients = Schema::hasTable('clients')
            ? Client::query()->latest()->get()
            : collect();
        $defaultCompany = Schema::hasTable('company_details')
            ? CompanyDetail::query()->where('is_default', true)->first()
            : null;
        $userColumns = ['id', 'name', 'images', 'designation'];
        if (Schema::hasColumn('users', 'photo')) {
            $userColumns[] = 'photo';
        }
        $signatories = Schema::hasTable('users')
            ? User::query()->orderBy('name')->get($userColumns)
            : collect();
        $products = Schema::hasTable('products')
            ? Product::query()->get()
            : collect();

        return view('frontend.pages.quotations.create', compact('clients', 'products', 'signatories', 'defaultCompany'));
    }

    public function store(Request $request)
{
    // Backward compatibility for legacy discount keys
    if (!$request->has('discount_amount')) {
        $fallbackDiscount = $request->input('overall_discount', $request->input('overall_disocunt'));
        if ($fallbackDiscount !== null) {
            $request->merge(['discount_amount' => $fallbackDiscount]);
        }
    }

    // Get default company and logo
    $defaultCompany = CompanyDetail::where('is_default', true)->first();
    $companyLogo = $defaultCompany?->photo;

    // Validation rules
    $rules = [
        'client_mode' => 'required|in:existing,new',
        'client_id' => 'nullable|integer',
        'client_name' => 'nullable|string|max:255',
        'client_designation' => 'nullable|string|max:255',
        'client_address' => 'nullable|string',
        'client_phone' => 'nullable|string|max:20',
        'client_email' => 'nullable|email|max:255',
        'highest_designation' => 'nullable|string|max:255',
        'attention_to' => 'nullable|string|max:255',
        'body_content' => 'nullable|string',
        'terms_conditions' => 'required|string',
        'subject' => 'nullable|string|max:255',
        'company_name' => 'required|string|max:255',
        'signatory_user_id' => 'required|integer|exists:users,id',
        'company_phone' => 'nullable|string|max:50',
        'company_email' => 'nullable|email|max:255',
        'company_website' => 'nullable|string|max:255',
        'company_address' => 'nullable|string',
        'additional_enclosed' => 'nullable|string',
        'discount_percent' => 'nullable|numeric|min:0|max:100',
        'vat_percent' => 'nullable|numeric|min:0',
        'tax_percent' => 'nullable|numeric|min:0',
        'installation_charge' => 'nullable|numeric|min:0',
        'round_off' => 'nullable|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        'items.*.description' => 'nullable|string',
    ];

    if (Schema::hasTable('clients')) {
        $rules['client_id'] = 'nullable|integer|exists:clients,id';
    }

    if (($request->input('client_mode') ?? 'new') === 'existing') {
        $rules['client_id'] = 'required|integer|exists:clients,id';
    } else {
        $rules['client_name'] = 'required|string|max:255';
        $rules['client_address'] = 'required|string';
    }

    $validated = $request->validate($rules);

    try {
        DB::transaction(function () use ($request, $companyLogo) {
            // Prepare client data
            $clientId = null;
            $clientName = $request->client_name ?? '';
            $clientAddress = $request->client_address ?? '';
            $clientPhone = $request->client_phone ?? '';
            $clientEmail = $request->client_email ?? '';

            // Signatory info
            $signatoryUser = User::findOrFail($request->signatory_user_id);
            $signatoryName = $signatoryUser->name;
            $signatoryDesignation = !empty($signatoryUser->designation) ? $signatoryUser->designation : (optional($signatoryUser->roles()->orderBy('name')->first())->name ?? '');
            $signatoryPhoto = null;
            if (!empty($signatoryUser->photo)) {
                $signatoryPhoto = $signatoryUser->photo;
            } elseif (!empty($signatoryUser->images)) {
                $signatoryPhoto = 'frontend/users/' . $signatoryUser->images;
            }

            // Handle client selection
            if (($request->input('client_mode') ?? 'new') === 'existing') {
                $client = Client::findOrFail($request->client_id);
                $clientId = $client->id;
                $clientName = $client->name;
                $clientAddress = $client->address;
                $clientPhone = $client->phone ?? '';
                $clientEmail = $client->email ?? '';
            } else {
                // Check if client already exists (by email or phone)
                $existingClient = Client::findByPhoneOrEmail($clientPhone, $clientEmail);
                
                if ($existingClient) {
                    // Reuse existing client
                    $client = $existingClient;
                    $clientId = $client->id;
                    $clientName = $client->name;
                    $clientAddress = $client->address;
                    $clientPhone = $client->phone ?? '';
                    $clientEmail = $client->email ?? '';
                } else {
                    // Create new client
                    $client = Client::create([
                        'name' => $clientName,
                        'highest_designation' => $request->highest_designation,
                        'phone' => $clientPhone ?: '-',
                        'email' => $clientEmail ?: ('no-email-' . now()->timestamp . '@local.test'),
                        'address' => $clientAddress,
                    ]);
                    $clientId = $client->id;
                }
            }

            // Calculate totals
            $subTotal = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineDiscountPercent = (float)($item['discount_percent'] ?? 0);
                $lineDiscount = $lineTotal * ($lineDiscountPercent / 100);
                $subTotal += ($lineTotal - $lineDiscount);
            }

            $discountPercent = (float)($request->discount_percent ?? 0);
            $discountAmount = $subTotal * ($discountPercent / 100);
            $vatPercent = (float)($request->vat_percent ?? 0);
            $taxPercent = (float)($request->tax_percent ?? 0);
            $installationCharge = (float)($request->installation_charge ?? 0);
            $roundOff = (float)($request->round_off ?? 0);

            $totalAfterDiscount = max(0, $subTotal - $discountAmount);
            $taxAmount = $totalAfterDiscount * ($taxPercent / 100);

            // Base for VAT: Subtotal - Discount + Installation - RoundOff
            $vatBase = max(0, $totalAfterDiscount + $installationCharge - $roundOff);
            $vatAmount = $vatBase * ($vatPercent / 100);

            $totalAmount = $vatBase + $vatAmount + $taxAmount;

            // Create quotation with snapshot fields
            $quotation = Quotation::create([
                'client_id' => $clientId,
                'quotation_date' => now(),
                'expiry_date' => now()->addDays(15),
                'notes' => $request->subject,
                'sub_total' => $subTotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'vat_percent' => $vatPercent,
                'vat_amount' => $vatAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'installation_charge' => $installationCharge,
                'round_off' => $roundOff,
                'total_amount' => $totalAmount,
                'status' => 'draft',

                // PDF snapshot fields
                'client_name' => $clientName,
                'highest_designation' => $request->highest_designation,
                'client_designation' => $request->client_designation,
                'client_address' => $clientAddress,
                'client_phone' => $clientPhone,
                'client_email' => $clientEmail,
                'attention_to' => $request->attention_to,
                'body_content' => $request->body_content,
                'terms_conditions' => $request->terms_conditions,
                'subject' => $request->subject,
                'company_name' => $request->company_name,
                'company_phone' => $request->company_phone,
                'company_email' => $request->company_email,
                'company_website' => $request->company_website,
                'company_address' => $request->company_address,
                'logo' => $companyLogo,
                'signatory_name' => $signatoryName,
                'signatory_designation' => $signatoryDesignation,
                'signatory_photo' => $signatoryPhoto,
                'additional_enclosed' => $request->additional_enclosed,
            ]);

            // Create quotation items
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineDiscountPercent = (float)($item['discount_percent'] ?? 0);
                $lineDiscount = $lineTotal * ($lineDiscountPercent / 100);
                $netTotal = $lineTotal - $lineDiscount;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $lineDiscountPercent,
                    'total' => $netTotal,
                    'description' => $item['description'] ?? null,
                ]);
            }
        });

    } catch (QueryException $e) {
        return back()->withInput()->with('warning', 'Unable to save quotation right now. Please check client setup and try again.');
    } catch (\Throwable $e) {
        return back()->withInput()->with('warning', 'Unable to save quotation right now. Please check client information and try again.');
    }

    return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
}

    public function getSuggestions(Request $request)
    {
        $clientId   = $request->query('client_id');
        $clientName = $request->query('client_name');

        $query = Quotation::query()->whereNotNull('attention_to');

        if ($clientId) {
            $query->where('client_id', $clientId);
        } elseif ($clientName) {
            $query->where('client_name', 'like', '%' . trim($clientName) . '%');
        }

        $rows = $query->select('attention_to', 'client_designation', 'highest_designation', 'created_at')
            ->latest()
            ->get()
            ->map(fn ($q) => [
                'attention_to'       => trim($q->attention_to),
                'client_designation' => $q->client_designation,
                'highest_designation'=> $q->highest_designation,
            ])
            ->filter(fn ($r) => $r['attention_to'] !== '')
            ->unique(fn ($r) => mb_strtolower($r['attention_to']))
            ->values();

        return response()->json($rows);
    }

    public function show(Quotation $quotation)
    {
        $attention_to = $quotation->attention_to;
        $quotation->load(['client', 'items.product']);
        return view('frontend.pages.quotations.show', compact('quotation', 'attention_to'));
    }

    public function edit(Quotation $quotation)
    {
        $clients = Schema::hasTable('clients')
            ? Client::query()->latest()->get()
            : collect();
        $defaultCompany = Schema::hasTable('company_details')
            ? CompanyDetail::query()->where('is_default', true)->first()
            : null;
        $userColumns = ['id', 'name', 'images', 'designation'];
        if (Schema::hasColumn('users', 'photo')) {
            $userColumns[] = 'photo';
        }
        $signatories = Schema::hasTable('users')
            ? User::query()->orderBy('name')->get($userColumns)
            : collect();
        $products = Schema::hasTable('products')
            ? Product::query()->get()
            : collect();
        $quotation->load('items.product');

        return view('frontend.pages.quotations.edit', compact('quotation', 'clients', 'products', 'signatories', 'defaultCompany'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        // Backward compatibility for legacy discount keys
        if (!$request->has('discount_amount')) {
            $fallbackDiscount = $request->input('overall_discount', $request->input('overall_disocunt'));
            if ($fallbackDiscount !== null) {
                $request->merge(['discount_amount' => $fallbackDiscount]);
            }
        }

        // Get default company and logo
        $defaultCompany = CompanyDetail::where('is_default', true)->first();
        $companyLogo = $defaultCompany?->photo;

        // Validation rules
        $rules = [
            'client_mode' => 'required|in:existing,new',
            'client_id' => 'nullable|integer',
            'client_name' => 'nullable|string|max:255',
            'client_designation' => 'nullable|string|max:255',
            'client_address' => 'nullable|string',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'highest_designation' => 'nullable|string|max:255',
            'attention_to' => 'nullable|string|max:255',
            'body_content' => 'nullable|string',
            'terms_conditions' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'signatory_user_id' => 'required|integer|exists:users,id',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'additional_enclosed' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'vat_percent' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'installation_charge' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.description' => 'nullable|string',
        ];

        if (Schema::hasTable('clients')) {
            $rules['client_id'] = 'nullable|integer|exists:clients,id';
        }

        if (($request->input('client_mode') ?? 'new') === 'existing') {
            $rules['client_id'] = 'required|integer|exists:clients,id';
        } else {
            $rules['client_name'] = 'required|string|max:255';
            $rules['client_address'] = 'required|string';
        }

        $validated = $request->validate($rules);

        try {
            DB::transaction(function () use ($request, $quotation, $companyLogo) {
                // Prepare client data
                $clientId = null;
                $clientName = $request->client_name ?? '';
                $clientAddress = $request->client_address ?? '';
                $clientPhone = $request->client_phone ?? '';
                $clientEmail = $request->client_email ?? '';

                // Signatory info
                $signatoryUser = User::findOrFail($request->signatory_user_id);
                $signatoryName = $signatoryUser->name;
                $signatoryDesignation = !empty($signatoryUser->designation) ? $signatoryUser->designation : (optional($signatoryUser->roles()->orderBy('name')->first())->name ?? '');
                $signatoryPhoto = null;
                if (!empty($signatoryUser->photo)) {
                    $signatoryPhoto = $signatoryUser->photo;
                } elseif (!empty($signatoryUser->images)) {
                    $signatoryPhoto = 'frontend/users/' . $signatoryUser->images;
                }

                // Handle client selection
                if (($request->input('client_mode') ?? 'new') === 'existing') {
                    $client = Client::findOrFail($request->client_id);
                    $clientId = $client->id;
                    $clientName = $client->name;
                    $clientAddress = $client->address;
                    $clientPhone = $client->phone ?? '';
                    $clientEmail = $client->email ?? '';
                } else {
                    // Check if client already exists (by email or phone)
                    $existingClient = Client::findByPhoneOrEmail($clientPhone, $clientEmail);
                    
                    if ($existingClient) {
                        // Reuse existing client
                        $client = $existingClient;
                        $clientId = $client->id;
                        $clientName = $client->name;
                        $clientAddress = $client->address;
                        $clientPhone = $client->phone ?? '';
                        $clientEmail = $client->email ?? '';
                    } else {
                        // Create new client
                        $client = Client::create([
                            'name' => $clientName,
                            'highest_designation' => $request->highest_designation,
                            'phone' => $clientPhone ?: '-',
                            'email' => $clientEmail ?: ('no-email-' . now()->timestamp . '@local.test'),
                            'address' => $clientAddress,
                        ]);
                        $clientId = $client->id;
                    }
                }

                // Calculate totals
                $subTotal = 0;
                foreach ($request->items as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $lineDiscountPercent = (float)($item['discount_percent'] ?? 0);
                    $lineDiscount = $lineTotal * ($lineDiscountPercent / 100);
                    $subTotal += ($lineTotal - $lineDiscount);
                }

                $discountPercent = (float)($request->discount_percent ?? 0);
                $discountAmount = $subTotal * ($discountPercent / 100);
                $vatPercent = (float)($request->vat_percent ?? 0);
                $taxPercent = (float)($request->tax_percent ?? 0);
                $installationCharge = (float)($request->installation_charge ?? 0);
                $roundOff = (float)($request->round_off ?? 0);

                $totalAfterDiscount = max(0, $subTotal - $discountAmount);
                $taxAmount = $totalAfterDiscount * ($taxPercent / 100);

                // Base for VAT: Subtotal - Discount + Installation - RoundOff
                $vatBase = max(0, $totalAfterDiscount + $installationCharge - $roundOff);
                $vatAmount = $vatBase * ($vatPercent / 100);

                $totalAmount = $vatBase + $vatAmount + $taxAmount;

                // Update quotation with snapshot fields
                $quotation->update([
                    'client_id' => $clientId,
                    'quotation_date' => now(),
                    'expiry_date' => now()->addDays(15),
                    'notes' => $request->subject,
                    'sub_total' => $subTotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'vat_percent' => $vatPercent,
                    'vat_amount' => $vatAmount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'installation_charge' => $installationCharge,
                    'round_off' => $roundOff,
                    'total_amount' => $totalAmount,
                    'status' => $quotation->status, // Keep existing status

                    // PDF snapshot fields
                    'client_name' => $clientName,
                    'highest_designation' => $request->highest_designation,
                    'client_designation' => $request->client_designation,
                    'client_address' => $clientAddress,
                    'client_phone' => $clientPhone,
                    'client_email' => $clientEmail,
                    'attention_to' => $request->attention_to,
                    'body_content' => $request->body_content,
                    'terms_conditions' => $request->terms_conditions,
                    'subject' => $request->subject,
                    'company_name' => $request->company_name,
                    'company_phone' => $request->company_phone,
                    'company_email' => $request->company_email,
                    'company_website' => $request->company_website,
                    'company_address' => $request->company_address,
                    'logo' => $companyLogo,
                    'signatory_name' => $signatoryName,
                    'signatory_designation' => $signatoryDesignation,
                    'signatory_photo' => $signatoryPhoto,
                    'additional_enclosed' => $request->additional_enclosed,
                ]);

                // Delete existing items
                $quotation->items()->delete();

                // Create new quotation items
                foreach ($request->items as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $lineDiscountPercent = (float)($item['discount_percent'] ?? 0);
                    $lineDiscount = $lineTotal * ($lineDiscountPercent / 100);
                    $netTotal = $lineTotal - $lineDiscount;

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_percent' => $lineDiscountPercent,
                        'total' => $netTotal,
                        'description' => $item['description'] ?? null,
                    ]);
                }
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('warning', 'Unable to update quotation right now. Please check client setup and try again.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('warning', 'Unable to update quotation right now. Please check client information and try again.');
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function download(Quotation $quotation)
    {
        $data = $this->preparePdfData($quotation);
        $pdf = Pdf::loadView('pdf.quotations', $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'portrait');
        return $pdf->download($quotation->quotation_number . '.pdf');
    }

    public function preview(Quotation $quotation)
    {
        $data = $this->preparePdfData($quotation);
        $pdf = Pdf::loadView('pdf.quotations', $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'portrait');
        return $pdf->stream($quotation->quotation_number . '.pdf');
    }

    // public function generatePDF(Quotation $quotation)
    // {
    //     $startedAt = hrtime(true);

    //     $quotation->load(['items.product', 'client']);
    //     $loadedAt = hrtime(true);

    //     $amount_in_words = $this->convertNumberToWords($quotation->total_amount) . ' Taka Only';

    //     $defaultCompany = CompanyDetail::where('is_default', true)->first();
    //     $companyLogo = $this->resolvePublicFilePath($quotation->logo ?: ($defaultCompany->photo ?? null));
    //     $pdfHeaderLogo = $this->optimizeImageForPdf(
    //         public_path('assets/invoice/assets/logo.png'),
    //         'packard-header-logo',
    //         420
    //     );
    //     $pdfGreenShape = $this->resolvePublicFilePath('assets/invoice/assets/left_side.png');
    //     $pdfBackgroundLogo = $this->resolvePublicFilePath('assets/invoice/assets/dotted-logo.png');
    //     $pdfPhoneIcon = $this->optimizeImageForPdf(
    //         public_path('assets/invoice/assets/telephone.png'),
    //         'packard-phone-icon',
    //         24
    //     );
    //     $pdfPhoneIcon = $this->makeIconWhiteForPdf($pdfPhoneIcon, 'packard-phone-icon-white');
    //     $pdfEmailIcon = $this->optimizeImageForPdf(
    //         public_path('assets/invoice/assets/email.png'),
    //         'packard-email-icon',
    //         24
    //     );
    //     $pdfEmailIcon = $this->makeIconWhiteForPdf($pdfEmailIcon, 'packard-email-icon-white');
    //     $pdfLocationIcon = $this->optimizeImageForPdf(
    //         public_path('assets/invoice/assets/location.png'),
    //         'packard-location-icon',
    //         24
    //     );
    //     $pdfLocationIcon = $this->makeIconWhiteForPdf($pdfLocationIcon, 'packard-location-icon-white');

    //     $signatoryPhotoRaw = $quotation->signatory_photo;
    //     if (empty($signatoryPhotoRaw) && !empty($quotation->signatory_user_id)) {
    //         $signatoryUser = User::query()
    //             ->select(['id', 'photo', 'images'])
    //             ->find($quotation->signatory_user_id);

    //         if ($signatoryUser) {
    //             if (!empty($signatoryUser->photo)) {
    //                 $signatoryPhotoRaw = $signatoryUser->photo;
    //             } elseif (!empty($signatoryUser->images)) {
    //                 $signatoryPhotoRaw = 'frontend/users/' . $signatoryUser->images;
    //             }
    //         }
    //     }

    //     $signatoryPhoto = $this->resolvePublicFilePath($signatoryPhotoRaw);
    //     $assetsResolvedAt = hrtime(true);

    //     $data = [
    //         'quotation' => $quotation,
    //         'amount_in_words' => $amount_in_words,

    //         // Client snapshot
    //         'client_name' => $quotation->client_name,
    //         'client_designation' => $quotation->client_designation,
    //         'client_address' => $quotation->client_address,
    //         'client_phone' => $quotation->client_phone,
    //         'client_email' => $quotation->client_email,

    //         // PDF content
    //         'attention_to' => $quotation->attention_to,
    //         'body_content' => $quotation->body_content,
    //         'terms_conditions' => $quotation->terms_conditions,
    //         'subject' => $quotation->subject,

    //         // Company snapshot
    //         'company_name' => $quotation->company_name,
    //         'company_phone' => $quotation->company_phone,
    //         'company_email' => $quotation->company_email,
    //         'company_website' => $quotation->company_website,
    //         'company_address' => $quotation->company_address,
    //         'company_logo' => $companyLogo,
    //         'pdf_header_logo' => $pdfHeaderLogo,
    //         'pdf_background_logo' => $pdfBackgroundLogo,
    //         'pdf_green_shape' => $pdfGreenShape,
    //         // 'pdf_dotted_background' => $pdfDottedBackground,
    //         'pdf_phone_icon' => $pdfPhoneIcon,
    //         'pdf_email_icon' => $pdfEmailIcon,
    //         'pdf_location_icon' => $pdfLocationIcon,

    //         // Signatory snapshot
    //         'signatory_name' => $quotation->signatory_name,
    //         'signatory_designation' => $quotation->signatory_designation,
    //         'signatory_photo' => $this->optimizeImageForPdf($signatoryPhoto, 'quotation-signatory-' . $quotation->id, 220),

    //         'additional_enclosed' => $quotation->additional_enclosed,
    //     ];

    //     $html = view('pdf.quotations', $data)->render();
    //     $viewRenderedAt = hrtime(true);

    //     $pdf = Pdf::loadHTML($html);

    //     $pdfBinary = $pdf->output();
    //     $pdfRenderedAt = hrtime(true);


    //     $timings = [
    //         'load_ms' => round(($loadedAt - $startedAt) / 1_000_000, 2),
    //         'asset_ms' => round(($assetsResolvedAt - $loadedAt) / 1_000_000, 2),
    //         'view_ms' => round(($viewRenderedAt - $assetsResolvedAt) / 1_000_000, 2),
    //         'pdf_ms' => round(($pdfRenderedAt - $viewRenderedAt) / 1_000_000, 2),
    //         'total_ms' => round(($pdfRenderedAt - $startedAt) / 1_000_000, 2),
    //     ];

    //     logger()->info('Quotation PDF timing', [
    //         'quotation_id' => $quotation->id,
    //         'quotation_number' => $quotation->quotation_number,
    //         'items_count' => $quotation->items->count(),
    //         'html_bytes' => strlen($html),
    //         'pdf_bytes' => strlen($pdfBinary),
    //         'header_logo_kb' => $this->fileSizeInKb($pdfHeaderLogo),
    //         'background_logo_kb' => $this->fileSizeInKb($pdfBackgroundLogo),
    //         'green_shape_kb' => $this->fileSizeInKb($pdfGreenShape),
    //         // 'dotted_background_kb' => $this->fileSizeInKb($pdfDottedBackground),
    //         'phone_icon_kb' => $this->fileSizeInKb($pdfPhoneIcon),
    //         'email_icon_kb' => $this->fileSizeInKb($pdfEmailIcon),
    //         'location_icon_kb' => $this->fileSizeInKb($pdfLocationIcon),
    //         'company_logo_kb' => $this->fileSizeInKb($companyLogo),
    //         'signatory_photo_kb' => $this->fileSizeInKb($signatoryPhoto),
    //         'timings_ms' => $timings,
    //     ]);

    //     return response($pdfBinary, 200, [
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' => 'attachment; filename="' . $quotation->quotation_number . '.pdf"',
    //         'X-Quotation-Pdf-Timing' => json_encode($timings),
    //     ]);
    // }

    public function generatePDF(Quotation $quotation)
    {
        $data = $this->preparePdfData($quotation);
        $pdf = Pdf::loadView('pdf.quotations', $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'portrait');
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $quotation->quotation_number . '.pdf"',
        ]);
    }

    private function preparePdfData(Quotation $quotation): array
    {
        $quotation->load(['items.product', 'client']);

        $amount_in_words = $this->convertNumberToWords($quotation->total_amount) . ' Taka Only';

        $pdfBackgroundImage = $this->optimizeImageForPdf(
            public_path('frontend/packard_bg_1.jpg'),
            'packard-background',
            1748,
            80
        );

        $signatoryUser = null;
        if (!empty($quotation->signatory_name)) {
            $signatoryUser = User::where('name', $quotation->signatory_name)->first();
        }

        $signatoryPhone = $signatoryUser?->phone;
        $signatoryEmail = $signatoryUser?->email;

        $signatoryPhotoRaw = $quotation->signatory_photo;
        if (empty($signatoryPhotoRaw) && $signatoryUser) {
            if (!empty($signatoryUser->photo)) {
                $signatoryPhotoRaw = $signatoryUser->photo;
            } elseif (!empty($signatoryUser->images)) {
                $signatoryPhotoRaw = 'frontend/users/' . $signatoryUser->images;
            }
        }

        $signatoryPhoto = $this->resolvePublicFilePath($signatoryPhotoRaw);

        return [
            'quotation' => $quotation,
            'amount_in_words' => $amount_in_words,

            // Client snapshot
            'client_name' => $quotation->client_name,
            'highest_designation' => $quotation->highest_designation,
            'client_designation' => $quotation->client_designation,
            'client_address' => $quotation->client_address,
            'client_phone' => $quotation->client_phone,
            'client_email' => $quotation->client_email,

            // PDF content
            'attention_to' => $quotation->attention_to,
            'body_content' => $quotation->body_content,
            'terms_conditions' => $quotation->terms_conditions,
            'subject' => $quotation->subject,

            // Company snapshot
            'company_name' => $quotation->company_name,
            'company_phone' => $quotation->company_phone,
            'company_email' => $quotation->company_email,
            'company_website' => $quotation->company_website,
            'company_address' => $quotation->company_address,

            // Background image
            'pdf_background_image' => $pdfBackgroundImage,

            // Signatory snapshot
            'signatory_name' => $quotation->signatory_name,
            'signatory_designation' => $quotation->signatory_designation,
            'signatory_photo' => $this->optimizeImageForPdf($signatoryPhoto, 'quotation-signatory-' . $quotation->id, 220),
            'signatory_phone' => $signatoryPhone,
            'signatory_email' => $signatoryEmail,

            'additional_enclosed' => $quotation->additional_enclosed,
        ];
    }

    private function resolvePublicFilePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);
        if (preg_match('/^https?:\/\//i', $path)) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            if (!empty($parsedPath)) {
                $path = $parsedPath;
            }
        }

        $path = str_replace('\\', '/', $path);
        $isAbsolute = preg_match('/^[A-Za-z]:\//', $path) === 1 || str_starts_with($path, '/');
        $fullPath = $isAbsolute ? $path : public_path(ltrim($path, '/'));

        return file_exists($fullPath) ? $fullPath : null;
    }

    private function fileSizeInKb(?string $path): ?float
    {
        if (!$path || !is_file($path)) {
            return null;
        }

        return round(filesize($path) / 1024, 2);
    }

    private function optimizeImageForPdf(?string $path, string $cacheKey, int $maxWidth = 420, int $jpegQuality = 82): ?string
    {
        if (!$path || !is_file($path)) {
            return null;
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo) {
            return $path;
        }

        [$width, $height] = $imageInfo;
        $mime = $imageInfo['mime'] ?? null;
        if (!$mime || $width < 1 || $height < 1) {
            return $path;
        }

        $cacheDir = storage_path('app/pdf-image-cache');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            default => null,
        };

        if (!$extension) {
            return $path;
        }

        $cacheHash = sha1(
            $cacheKey . '|' . $path . '|' . filemtime($path) . '|' . filesize($path) . '|' . sha1_file($path) . '|' . $maxWidth . '|' . $jpegQuality
        );
        $cachedPath = $cacheDir . DIRECTORY_SEPARATOR . $cacheHash . '.' . $extension;
        if (is_file($cachedPath)) {
            return $cachedPath;
        }

        $targetWidth = min($width, $maxWidth);
        $scale = $targetWidth / $width;
        $targetHeight = max(1, (int) round($height * $scale));

        $sourceImage = @imagecreatefromstring(file_get_contents($path));
        if (!$sourceImage) {
            return $path;
        }

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($mime === 'image/png') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            $white = imagecolorallocate($targetImage, 255, 255, 255);
            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $white);
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        if ($mime === 'image/png') {
            imagepng($targetImage, $cachedPath, 7);
        } else {
            imagejpeg($targetImage, $cachedPath, $jpegQuality);
        }

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return is_file($cachedPath) ? $cachedPath : $path;
    }

    private function makeIconWhiteForPdf(?string $path, string $cacheKey): ?string
    {
        if (!$path || !is_file($path)) {
            return null;
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo) {
            return $path;
        }

        [$width, $height] = $imageInfo;
        $cacheDir = storage_path('app/pdf-image-cache');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cachedPath = $cacheDir . DIRECTORY_SEPARATOR . sha1(
            $cacheKey . '|' . $path . '|' . filemtime($path) . '|' . filesize($path) . '|' . sha1_file($path)
        ) . '.png';
        if (is_file($cachedPath)) {
            return $cachedPath;
        }

        $sourceImage = @imagecreatefromstring(file_get_contents($path));
        if (!$sourceImage) {
            return $path;
        }

        $targetImage = imagecreatetruecolor($width, $height);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $width, $height, $transparent);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($sourceImage, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $color = imagecolorallocatealpha($targetImage, 255, 255, 255, $alpha);
                imagesetpixel($targetImage, $x, $y, $color);
            }
        }

        imagepng($targetImage, $cachedPath, 7);
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return is_file($cachedPath) ? $cachedPath : $path;
    }

    public function sendQuotation(Quotation $quotation)
    {
        $quotation->update(['status' => 'sent']);
        return redirect()->back()->with('success', 'Quotation sent successfully.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate([
            'status' => 'required|in:sent,accepted,rejected,expired'
        ]);

        $quotation->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Quotation status updated successfully.');
    }

    public function getProduct($id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'product' => $product,
            'unit_price' => $product->price ?? 0
        ]);
    }

    private function convertNumberToWords($number)
    {
        if ($number == 0) {
            return 'Zero';
        }

        $ones = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        );

        $tens = array(
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        );

        $words = '';

        // Handle crores
        if ($number >= 10000000) {
            $crores = floor($number / 10000000);
            $words .= $this->convertNumberToWords($crores) . ' Crore ';
            $number %= 10000000; // ADDED THIS LINE
        } // ADDED MISSING CLOSING BRACE HERE

        // Handle lakhs
        if ($number >= 100000) {
            $lakhs = floor($number / 100000);
            $words .= $this->convertNumberToWords($lakhs) . ' Lakh ';
            $number %= 100000;
        }

        // Handle thousands
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $words .= $this->convertNumberToWords($thousands) . ' Thousand ';
            $number %= 1000;
        }

        // Handle hundreds
        if ($number >= 100) {
            $hundreds = floor($number / 100);
            $words .= $this->convertNumberToWords($hundreds) . ' Hundred ';
            $number %= 100;
        }

        // Handle tens and ones
        if ($number > 0) {
            if ($number < 20) {
                $words .= $ones[$number];
            } else {
                $words .= $tens[floor($number / 10)];
                if ($number % 10 > 0) {
                    $words .= ' ' . $ones[$number % 10];
                }
            }
        }

        return trim($words);
    }

}
