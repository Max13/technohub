<?php

namespace App\Console\Commands\Accounting;

use App\Imports\TransactionsImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:payments {file : Excel filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import payments from the given excel file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!is_readable($this->argument('file'))) {
            $this->error('File not readable');
            return 1;
        }

        Excel::import(new TransactionsImport, $this->argument('file'));

        return 0;
    }
}
