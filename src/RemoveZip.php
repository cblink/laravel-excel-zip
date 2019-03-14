<?php


namespace Cblink\ExcelZip;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $folder;

    /**
     * Create a new job instance.
     *
     * @param $folder
     */
    public function __construct($folder)
    {
        $this->folder = $folder;

        $this->onConnection(config('excel_zip.queue.connection'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        array_map('unlink', glob(storage_path("app/$this->folder")."/*"));

        rmdir(storage_path("app/$this->folder"));
    }
}
