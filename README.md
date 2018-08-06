# laravel-excel-zip
download excels wrap in zip

laravel-excel-zip is a solution to download a large excel. You can define when to chunk excels into a zip file.

## Install

`composer require cblink/laravel-excel-zip -vvv`

## Usage

run command to create `config/excel_zip.php`

`php artisan vendor:publish --provider="Cblink\ExcelZip\ExcelZipServiceProvider"`

### Export

use CustomCollection in your `Export`

```php
<?php

use Cblink\ExcelZip\CustomCollection;
use Maatwebsite\Excel\Concerns\FromCollection;

class MemberExport implements FromCollection
{
    use CustomCollection;
}
```

### Controller

```php
<?php

use Cblink\ExcelZip\ExcelZip;
use App\Http\Controllers\Controller;

class MemberController extends Controller
{
    public function export(ExcelZip $excelZip, MemberExport $export)
    {
        return $excelZip->download(Member::all(), $export);
    }
}
```