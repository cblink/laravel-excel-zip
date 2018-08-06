<?php


namespace Cblink\ExcelZip;


use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;

class ExcelZip
{

    /**
     * excel instance
     *
     * @var Excel
     */
    private $excel;

    /**
     * folder name
     *
     * @var string
     */
    private $folder;

    /**
     * excel export
     *
     * @var
     */
    private $export;

    /**
     * excel counter
     *
     * @var int
     */
    private $counter = 1;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->folder = 'member_'.str_random(6);
    }

    /**
     * set a excel export
     *
     * @param $export
     * @return $this
     */
    public function setExport($export)
    {
        $this->export = $export;

        return $this;
    }

    /**
     * store a excel file
     *
     * @param Collection $collection
     * @param string $fileName
     * @param null $export
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function excel(Collection $collection, string $fileName = null, $export = null)
    {
        $this->export = $export ?: $this->export;
        $fileName = $fileName ? $fileName.'_'.$this->counter : $this->counter;

        $this->excel->store($this->export->setCollection($collection), "{$this->folder}/$fileName.xlsx");

        $this->counter++;

        return $this;
    }

    /**
     * download zip
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function zip()
    {
        $this->generateZip();

        return $this->response(storage_path($this->folder.'.zip'));
    }

    /**
     * @param Collection $collection
     * @param CustomCollection $export
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Exception
     */
    public function download(Collection $collection, $export, string $fileName = 'download')
    {
        $chunk = config('excel_zip.chunk', 5000);

        if (!config('excel_zip.always_zip', false) && $collection->count() < $chunk) {
            return $this->excel->download($export->setCollection($collection), $fileName.'.xlsx')->deleteFileAfterSend(true);
        }

        foreach ($collection->chunk($chunk) as $key => $members) {
            $this->excel->store($export->setCollection($members), "{$this->folder}/$fileName-$key.xlsx", 'local');
        }

        return $this->zip();
    }

    /**
     * generate the zip file
     *
     * @throws \Exception
     */
    private function generateZip()
    {
        $zipper = new Zipper();

        $zipper->make(storage_path("{$this->folder}.zip"))->add(glob(storage_path("app/{$this->folder}").'/*'))->close();

        dispatch(new RemoveZip($this->folder))->delay(Carbon::now()->addMinute());
    }

    /**
     * return response
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function response(string $path)
    {
        $this->reset();

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * reset folder
     */
    private function reset()
    {
        $this->folder = null;
        $this->counter = 1;
    }

}