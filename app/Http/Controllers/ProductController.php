<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\Rule;
use App\Models\Product;
use ZipArchive;

class ProductController extends Controller
{
    private function hasProductsTable(): bool
    {
        return Schema::hasTable('products');
    }

    private function missingProductsTableMessage(): string
    {
        return "The 'products' table is missing. Import DB or run migrations.";
    }

    // Show all products
   public function index(Request $request)
{
    if (!$this->hasProductsTable()) {
        $products = new LengthAwarePaginator(
            [],
            0,
            10,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('frontend.pages.products.index', compact('products'))
            ->with('warning', $this->missingProductsTableMessage());
    }

    // Start query
    $query = Product::query();

    // Apply filters if present
    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    if ($request->filled('product_code')) {
        $query->where('product_code', 'like', '%' . $request->product_code . '%');
    }

    $allowedSorts = ['name', 'product_code', 'unit', 'price', 'created_at'];
    $sortBy = $request->get('sort_by', 'created_at');
    $sortDir = strtolower((string)$request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

    if (!in_array($sortBy, $allowedSorts, true)) {
        $sortBy = 'created_at';
    }

    $products = $query
        ->orderBy($sortBy, $sortDir)
        ->paginate(15)
        ->withQueryString();

    return view('frontend.pages.products.index', compact('products'));
}

    // Show create form
    public function create()
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        return view('frontend.pages.products.create');
    }

    // Store new product
    public function store(Request $request)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255|unique:products',
            'details' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            Product::create($request->all());
        } catch (QueryException $e) {
            return back()->withInput()->with('warning', 'Unable to save product right now.');
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    // Show single product
    public function show($id)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('warning', 'Product not found.');
        }

        return view('frontend.pages.products.show', compact('product'));
    }

    // Show edit form
    public function edit($id)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('warning', 'Product not found.');
        }

        return view('frontend.pages.products.edit', compact('product'));
    }

    // Update product
    public function update(Request $request, $id)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('warning', 'Product not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => ['required', 'string', 'max:255', Rule::unique('products', 'product_code')->ignore($product->id)],
            'details' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $product->update($request->all());
        } catch (QueryException $e) {
            return back()->withInput()->with('warning', 'Unable to update product right now.');
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    // Delete product
    public function destroy($id)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index')->with('warning', 'Product not found.');
        }

        try {
            $product->delete();
        } catch (QueryException $e) {
            return redirect()->route('products.index')->with('warning', 'Unable to delete product right now.');
        }

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function export(Request $request)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $query = Product::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('product_code')) {
            $query->where('product_code', 'like', '%' . $request->product_code . '%');
        }

        $allowedSorts = ['name', 'product_code', 'unit', 'price', 'created_at'];
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = strtolower((string)$request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $headers = ['SL', 'Name', 'Product Code', 'Unit', 'Price', 'Details'];

        $rows = [];
        $sl = 1;
        $products = $query->orderBy($sortBy, $sortDir)->get();

        foreach ($products as $product) {
            $rows[] = [
                $sl++,
                $product->name,
                (string)$product->product_code,
                $product->unit,
                $product->price !== null ? (float)$product->price : '',
                $product->details ?? '',
            ];
        }

        $fileName = 'products_' . date('Y-m-d') . '.xlsx';

        return \App\Helpers\SimpleXlsxExporter::download($fileName, $headers, $rows);
    }

    public function import(Request $request)
    {
        if (!$this->hasProductsTable()) {
            return redirect()->route('products.index')
                ->with('warning', $this->missingProductsTableMessage());
        }

        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
        ]);

        $file = $request->file('import_file');
        $ext = strtolower((string)$file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            $rows = $ext === 'xlsx'
                ? $this->parseXlsxRows($path)
                : $this->parseCsvRows($path);
        } catch (\Throwable $e) {
            return redirect()->route('products.index')
                ->with('warning', 'Unable to read import file. Please check file format.');
        }

        if (count($rows) === 0) {
            return redirect()->route('products.index')
                ->with('warning', 'No data rows found in import file.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $line => $row) {
            $name = trim((string)($row['name'] ?? $row[1] ?? $row[2] ?? ''));
            $code = trim((string)($row['product_code'] ?? $row[2] ?? $row[3] ?? ''));
            $unit = trim((string)($row['unit'] ?? $row[3] ?? $row[4] ?? ''));
            $priceRaw = trim((string)($row['price'] ?? $row[4] ?? $row[5] ?? ''));
            $details = isset($row['details']) ? trim((string)$row['details']) : (isset($row[5]) ? trim((string)$row[5]) : (isset($row[6]) ? trim((string)$row[6]) : null));

            // Remove everything except digits and decimal point.
            $priceClean = preg_replace('/[^0-9.]/', '', $priceRaw);
            $price = (is_numeric($priceClean) && $priceClean !== '') ? (float)$priceClean : null;

            if ($name === '' && $code === '' && $unit === '' && ($priceRaw === '' || $priceRaw === '0')) {
                $skipped++;
                continue;
            }

            if ($name === '' || $code === '' || $unit === '' || $price === null || $price < 0) {
                $missing = [];
                if ($name === '') $missing[] = 'name';
                if ($code === '') $missing[] = 'product_code';
                if ($unit === '') $missing[] = 'unit';
                if ($price === null) $missing[] = 'price';

                $errors[] = "Row {$line}: missing or invalid " . implode(', ', $missing) . ".";
                continue;
            }

            if (mb_strlen($name) > 255) {
                $name = mb_substr($name, 0, 255);
            }

            if (mb_strlen($code) > 255 || mb_strlen($unit) > 255) {
                $tooLong = [];
                if (mb_strlen($code) > 255) $tooLong[] = 'product_code';
                if (mb_strlen($unit) > 255) $tooLong[] = 'unit';
                $errors[] = "Row {$line}: value too long for " . implode(', ', $tooLong) . ".";
                continue;
            }

            if ($price > 99999999.99) {
                $errors[] = "Row {$line}: price exceeds allowed limit.";
                continue;
            }

            $payload = [
                'name' => $name,
                'details' => $details ?: null,
                'unit' => $unit,
                'price' => $price,
            ];

            try {
                $existing = Product::where('product_code', $code)->first();
                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    $payload['product_code'] = $code;
                    Product::create($payload);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$line}: database save failed for product code '{$code}'.";
                continue;
            }
        }

        $message = "Import completed. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.";
        if (!empty($errors)) {
            $message .= ' Some rows had errors.';
        }

        return redirect()->route('products.index')
            ->with('success', $message)
            ->with('import_errors', array_slice($errors, 0, 50));
    }

    private function parseCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open CSV');
        }

        $header = null;
        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(fn($h) => $this->normalizeHeader((string)$h), $data);
                $line++;
                continue;
            }

            if (count(array_filter($data, fn($v) => trim((string)$v) !== '')) === 0) {
                $line++;
                continue;
            }

            $row = [];
            foreach ($header as $idx => $key) {
                if ($key === '') {
                    continue;
                }
                $row[$key] = $data[$idx] ?? null;
            }
            $rows[$line] = $row;
            $line++;
        }
        fclose($handle);

        return $rows;
    }

    private function parseXlsxRows(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new \RuntimeException('Cannot open XLSX');
    }

        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        $sheetCandidates = ['xl/worksheets/sheet1.xml'];
        if ($workbookXml !== false && $workbookRelsXml !== false) {
            $sheetCandidates = $this->extractWorksheetPaths($workbookXml, $workbookRelsXml);
            if (empty($sheetCandidates)) {
                $sheetCandidates = ['xl/worksheets/sheet1.xml'];
            }
        }

        $sheetXml = false;
        $bestScore = -1;
        foreach ($sheetCandidates as $sheetPath) {
            $candidateXml = $zip->getFromName($sheetPath);
            if ($candidateXml === false) {
                continue;
            }
            $score = $this->scoreWorksheetHeader($candidateXml, $sharedXml ?: '');
            if ($score > $bestScore) {
                $bestScore = $score;
                $sheetXml = $candidateXml;
            }
        }
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('Sheet not found');
        }

    // Parse shared strings
    $shared = [];
    if ($sharedXml !== false) {
        $dom = new \DOMDocument();
        @$dom->loadXML($sharedXml);
        foreach ($dom->getElementsByTagName('si') as $si) {
            $text = '';
            foreach ($si->getElementsByTagName('t') as $t) {
                $text .= $t->nodeValue;
            }
            $shared[] = $text;
        }
    }

    // Parse sheet
    $sheetDom = new \DOMDocument();
    if (!@$sheetDom->loadXML($sheetXml)) {
        throw new \RuntimeException('Cannot parse sheet XML');
    }

    $headerByIndex = [];
    $rows = [];
    $line = 0;

    foreach ($sheetDom->getElementsByTagName('row') as $rowNode) {
        $line++;
        $cells = [];

        foreach ($rowNode->getElementsByTagName('c') as $c) {
            $ref  = $c->getAttribute('r');
            $type = $c->getAttribute('t');
            $idx  = $this->columnIndexFromRef($ref);

            $vNode = $c->getElementsByTagName('v')->item(0);
            $rawVal = $vNode ? $vNode->nodeValue : '';

            if ($type === 's') {
                $value = $shared[(int)$rawVal] ?? '';
            } elseif ($type === 'inlineStr') {
                $tNode = $c->getElementsByTagName('t')->item(0);
                $value = $tNode ? $tNode->nodeValue : '';
            } else {
                $value = $rawVal;
            }

            $cells[$idx] = $value;
        }

        if ($line === 1) {
            foreach ($cells as $idx => $value) {
                $headerByIndex[$idx] = $this->normalizeHeader($value);
            }
            continue;
        }

        if (count(array_filter($cells, fn($v) => trim((string)$v) !== '')) === 0) {
            continue;
        }

        $mapped = [];
        foreach ($headerByIndex as $idx => $key) {
            if ($key === '') continue;
            $mapped[$key] = $cells[$idx] ?? null;
        }
        foreach ($cells as $idx => $value) {
            $mapped[$idx] = $value;
        }
        $rows[$line] = $mapped;
    }

    return $rows;
}

    private function normalizeHeader(string $header): string
    {
        $key = strtolower(trim($header));
        // Remove any characters that aren't alphanumeric, spaces, or hyphens
        $key = preg_replace('/[^a-z0-9\s\-]/', '', $key);
        $key = str_replace([' ', '-'], '_', $key);
        return $key;
    }

    private function columnIndexFromRef(string $ref): int
    {
        preg_match('/^[A-Z]+/i', $ref, $m);
        $letters = strtoupper($m[0] ?? 'A');
        $index = 0;
        foreach (str_split($letters) as $ch) {
            $index = ($index * 26) + (ord($ch) - 64);
        }
        return max(1, $index);
    }

    private function extractWorksheetPaths(string $workbookXml, string $workbookRelsXml): array
    {
        $paths = [];

        $wbDom = new \DOMDocument();
        $relsDom = new \DOMDocument();
        if (!@$wbDom->loadXML($workbookXml) || !@$relsDom->loadXML($workbookRelsXml)) {
            return $paths;
        }

        $wbXpath = new \DOMXPath($wbDom);
        $wbXpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $relsXpath = new \DOMXPath($relsDom);
        $relsXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relMap = [];
        foreach ($relsXpath->query('//r:Relationship') as $relNode) {
            $id = $relNode->attributes?->getNamedItem('Id')?->nodeValue ?? '';
            $target = $relNode->attributes?->getNamedItem('Target')?->nodeValue ?? '';
            if ($id !== '' && $target !== '') {
                $relMap[$id] = 'xl/' . ltrim(str_replace('\\', '/', $target), '/');
            }
        }

        foreach ($wbXpath->query('//x:sheets/x:sheet') as $sheetNode) {
            $rid = $sheetNode->attributes?->getNamedItemNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id')?->nodeValue
                ?? $sheetNode->attributes?->getNamedItem('r:id')?->nodeValue
                ?? '';
            if ($rid !== '' && isset($relMap[$rid])) {
                $paths[] = $relMap[$rid];
            }
        }

        return $paths;
    }

    private function scoreWorksheetHeader(string $sheetXml, string $sharedXml): int
    {
        $shared = [];
        if ($sharedXml !== '') {
            $dom = new \DOMDocument();
            if (@$dom->loadXML($sharedXml)) {
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($xpath->query('//x:si') as $si) {
                    $text = '';
                    foreach ($xpath->query('.//x:t', $si) as $t) {
                        $text .= $t->nodeValue;
                    }
                    $shared[] = $text;
                }
            }
        }

        $dom = new \DOMDocument();
        if (!@$dom->loadXML($sheetXml)) {
            return -1;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $firstRow = $xpath->query('//x:sheetData/x:row[1]')->item(0);
        if (!$firstRow) {
            return -1;
        }

        $headers = [];
        foreach ($xpath->query('./x:c', $firstRow) as $cellNode) {
            $type = $cellNode->getAttribute('t');
            $value = '';

            if ($type === 's') {
                $vNode = $xpath->query('./x:v', $cellNode)->item(0);
                $si = $vNode ? (int)$vNode->textContent : -1;
                $value = $shared[$si] ?? '';
            } elseif ($type === 'inlineStr') {
                $parts = [];
                foreach ($xpath->query('./x:is//x:t', $cellNode) as $tNode) {
                    $parts[] = $tNode->textContent;
                }
                $value = implode('', $parts);
            } else {
                $vNode = $xpath->query('./x:v', $cellNode)->item(0);
                $value = $vNode ? (string)$vNode->textContent : '';
            }

            $headers[] = $this->normalizeHeader($value);
        }

        $headerSet = array_fill_keys($headers, true);
        $score = 0;
        foreach (['name', 'product_code', 'unit', 'price', 'details'] as $expected) {
            if (isset($headerSet[$expected])) {
                $score++;
            }
        }

        return $score;
    }
}
