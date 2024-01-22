<?php

namespace App\Console\Commands\Hotspot;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Hotspot deploy command
 *
 * alogin.html:   Page shown after client has logged in.
 *                It pops-up status page (if any) and redirects to originally requested page
 * login.html:    Login page shown to a user to ask for username and password
 * redirect.html: Redirects user to another url (for example, to login page)
 *
 * @link https://help.mikrotik.com/docs/display/ROS/Hotspot+customisation
 */
class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotspot:deploy {authUrl? : Base URL to use for the login/redirect files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and deploy login/redirect files to the hotspot'.PHP_EOL
                            .'(using the default hotspot filesystem, or env variables: '
                            .'HOTSPOT_HOST, HOTSPOT_USERNAME, HOTSPOT_PASSWORD';

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $tmpDisk;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $tmpPath = sys_get_temp_dir().'/'.Str::random(10);
        throw_if(!mkdir($tmpPath), RuntimeException::class, 'Could not create temporary directory');
        $this->tmpDisk = Storage::build([
            'driver' => 'local',
            'root' => $tmpPath,
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Variables
        $authUrl = $this->argument('authUrl') ?? config('app.url');
        $queryStr = 'captive=$(link-login-only-esc)&dst=$(link-orig-esc)&hs=$(server-name-esc)&ip=$(ip-esc)&mac=$(mac-esc)';
        $toReplace = [
            '/{{ ?loginUrl ?}}/' => $authUrl.route('hotspot.redirectToLogin', [], false).'?'.$queryStr,
            '/{{ ?loggedInUrl ?}}/' => $authUrl.route('hotspot.showConnected', [], false).'?'.$queryStr,
        ];

        // Write temporary files
        $this->line('Writing temporary files:');
        $this->withProgressBar(Storage::disk('stubs')->files('hotspot/mikrotik'), function ($stub) use ($toReplace) {
            $content = Storage::disk('stubs')->get($stub);
            $tmpFile = basename($stub);
            throw_if(
                !$this->tmpDisk->put($tmpFile, preg_replace(array_keys($toReplace), array_values($toReplace), $content)),
                RuntimeException::class,
                'Could not write to temporary file'
            );
        });
        $this->newLine(2);

        // Deploy files
        throw_if(
            !Storage::disk('hotspot')->makeDirectory('hs'),
            RuntimeException::class,
            'Could not create target hotspot directory'
        );
        $this->line('Deploying login/redirect files to hotspot:');
        $this->withProgressBar($this->tmpDisk->files(), function ($file) {
            Storage::disk('hotspot')->putFileAs('hs', $this->tmpDisk->path($file), $file);
        });
        $this->newLine(2);

        return 0;
    }
}
