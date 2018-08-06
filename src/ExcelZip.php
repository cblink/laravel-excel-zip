<?php


namespace Cblink\ExcelZip;


use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;

class ExcelZip
{

    /**
     * @param Collection $collection
     * @param Excel $excel
     * @param CustomCollection $export
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     */
    public function download(Collection $collection, Excel $excel, CustomCollection $export, string $fileName = 'download')
    {
        $folder = 'member_'.str_random(6);

        $chunk = config('excel_zip.chunk', 5000);

        if (!config('excel_zip.always_zip', false) && $collection->count() < $chunk) {
            return response()->download($export->setCollection($collection), $fileName.'.xlsx');
        }

        foreach ($collection->chunk($chunk) as $key => $members) {
            $excel->store($export->setCollection($members), "$folder/$fileName-$key.xlsx", 'local');
        }

        $zipper = new Zipper();

        $zipper->make(storage_path("$folder.zip"))->add(glob(storage_path("app/$folder").'/*'))->close();

        dispatch(new RemoveZip($folder))->delay(Carbon::now()->addMinute());

        return response()->download(storage_path($folder.'.zip'));
    }

}