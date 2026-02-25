<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Models\UpdateLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laramin\Utility\VugiChugi;
use App\Traits\MasksSensitiveData;

class SystemController extends Controller
{
    use MasksSensitiveData;
    public function systemInfo(){
        $laravelVersion = app()->version();
        $timeZone = config('app.timezone');
        $pageTitle = 'Application Information';
        return view('admin.system.info',compact('pageTitle', 'laravelVersion','timeZone'));
    }

    public function optimize(){
        $pageTitle = 'Clear System Cache';
        return view('admin.system.optimize',compact('pageTitle'));
    }

    public function optimizeClear(){
        Artisan::call('optimize:clear');
        $notify[] = ['success','Cache cleared successfully'];
        return back()->withNotify($notify);
    }

    public function systemServerInfo(){
        $currentPHP = phpversion();
        $pageTitle = 'Server Information';
        $serverDetails = $_SERVER;
        
        // Filter out sensitive server information if demo user
        if (auth('admin')->user() && auth('admin')->user()->isDemoUser()) {
            // Remove sensitive keys from server details
            $sensitiveKeys = [
                'DB_PASSWORD', 'DATABASE_PASSWORD', 'REDIS_PASSWORD', 'MAIL_PASSWORD',
                'AWS_SECRET_ACCESS_KEY', 'ENVATO_PERSONAL_TOKEN', 'APP_KEY'
            ];
            foreach ($sensitiveKeys as $key) {
                if (isset($serverDetails[$key])) {
                    $serverDetails[$key] = '{protected in demo mode}';
                }
            }
        }
        
        return view('admin.system.server',compact('pageTitle', 'currentPHP', 'serverDetails'));
    }

    public function systemUpdate() {
        $pageTitle = 'System Updates';
        return view('admin.system.update',compact('pageTitle'));
    }


    public function systemUpdateProcess(){
        if (gs('system_customized')) {
            return response()->json([
                'status'=>'error',
                'message'=>[
                    'The system already customized. You can\'t update the project'
                ]
            ]);
        }


        if (version_compare(systemDetails()['version'],gs('available_version'),'==')) {
            return response()->json([
                'status'=>'info',
                'message'=>[
                    'The system is currently up to date'
                ]
            ]);
        }


        if(!extension_loaded('zip')){
            return response()->json([
                'status'=>'error',
                'message'=>[
                    'Zip Extension is required to update the system'
                ]
            ]);
        }

        $purchasecode = env('PURCHASECODE');
        if (!$purchasecode) {
            return response()->json([
                'status'=>'error',
                'message'=>[
                    'Invalid request. Please contact with support'
                ]
            ]);
        }

        $website = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");

        $response = CurlRequest::curlPostContent(VugiChugi::upman(),[
            'purchasecode'=>$purchasecode,
            'product'=>systemDetails()['name'],
            'version'=>systemDetails()['version'],
            'website'=>$website,
        ]);

        $response = json_decode($response);
        if($response->status == 'error'){
            return response()->json([
                'status'=>'error',
                'message'=>$response->message->error
            ]);
        }

        if($response->remark == 'already_updated'){
            return response()->json([
                'status'=>'info',
                'message'=>$response->message->success
            ]);
        }

        $directory = 'core/temp/';
        $files = [];
        foreach($response->data->files as $key => $fileUrl){

            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Purchase-Code: $purchasecode"
                ]
            ];

            $context = stream_context_create($opts);
            $fileContent = file_get_contents($fileUrl,false,$context);

            if(@json_decode($fileContent)->status == 'error'){
                return response()->json([
                    'status'=>'error',
                    'message'=>@json_decode($fileContent)->message->error
                ]);
            }
            file_put_contents($directory.$key.'.zip',$fileContent);
            $files[$key] = $fileContent;
        }

        $fileNames = array_keys($files);
        foreach($fileNames as $fileName){
            $rand    = Str::random(10);
            $dir     = base_path('temp/' . $rand);
            $extract = $this->extractZip(base_path('temp/' . $fileName.'.zip'), $dir);

            if ($extract == false) {
                $this->removeDir($dir);
                return response()->json([
                    'status'=>'error',
                    'message'=>['Something went wrong while extracting the update']
                ]);
            }

            if (!file_exists($dir . '/config.json')) {
                $this->removeDir($dir);
                return response()->json([
                    'status'=>'error',
                    'message'=>['Config file not found']
                ]);
            }

            $getConfig = file_get_contents($dir . '/config.json');
            $config    = json_decode($getConfig);

            $this->removeFile($directory . '/' . $fileName.'.zip');

            $mainFile = $dir . '/update.zip';
            if (!file_exists($mainFile)) {
                $this->removeDir($dir);
                return response()->json([
                    'status'=>'error',
                    'message'=>['Something went wrong while patching the update']
                ]);
            }


            //move file
            $extract = $this->extractZip(base_path('temp/' . $rand . '/update.zip'), base_path('../'));
            if ($extract == false) {
                return response()->json([
                    'status'=>'error',
                    'message'=>['Something went wrong while extracting the update']
                ]);
            }



            //Execute database
            if (file_exists($dir . '/update.sql')) {
                $sql = file_get_contents($dir . '/update.sql');
                DB::unprepared($sql);
            }

            $updateLog = new UpdateLog();
            $updateLog->version = $config->version;
            $updateLog->update_log = $config->changes;
            $updateLog->save();

            $this->removeDir($dir);

        }
        Artisan::call('optimize:clear');
        return response()->json([
            'status'=>'success',
            'message'=>['System updated successfully']
        ]);
    }

    public function systemUpdateLog(){
        $pageTitle = 'System Update Log';
        $updates = UpdateLog::orderBy('id','desc')->paginate(getPaginate());
        return view('admin.system.update_log',compact('pageTitle','updates'));
    }

    protected function extractZip($file, $extractTo)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($file);
        if ($res != true) {
            return false;
        }

        for( $i = 0 ; $i < $zip->numFiles ; $i++ ) {
            if ( $zip->getNameIndex( $i ) != '/' && $zip->getNameIndex( $i ) != '__MACOSX/_' ) {
                $zip->extractTo( $extractTo, array($zip->getNameIndex($i)) );
            }
        }

        $zip->close();
        return true;
    }

    protected function removeFile($path)
    {
        $fileManager = new FileManager();
        $fileManager->removeFile($path);
    }

    protected function removeDir($location)
    {
        $fileManager = new FileManager();
        $fileManager->removeDirectory($location);
    }

    public function installComposer()
    {
        $pageTitle = 'Install Composer Dependencies';
        return view('admin.system.composer', compact('pageTitle'));
    }

    public function installComposerProcess()
    {
        try {
            // Check if composer is available via different methods
            $composerAvailable = false;
            $composerPath = '';

            // Check system composer
            $composerCheck = shell_exec('composer --version 2>&1');
            if ($composerCheck && strpos($composerCheck, 'Composer') !== false) {
                $composerAvailable = true;
                $composerPath = 'composer';
            }

            // Check for composer.phar in project root
            if (!$composerAvailable && file_exists(base_path('composer.phar'))) {
                $composerCheck = shell_exec('php ' . base_path('composer.phar') . ' --version 2>&1');
                if ($composerCheck && strpos($composerCheck, 'Composer') !== false) {
                    $composerAvailable = true;
                    $composerPath = 'php ' . base_path('composer.phar');
                }
            }

            // Check for global composer.phar
            if (!$composerAvailable) {
                $composerCheck = shell_exec('php composer.phar --version 2>&1');
                if ($composerCheck && strpos($composerCheck, 'Composer') !== false) {
                    $composerAvailable = true;
                    $composerPath = 'php composer.phar';
                }
            }

            if (!$composerAvailable) {
                $notify[] = ['error', 'Composer is not available. Please install composer or upload composer.phar to your project root.'];
                $notify[] = ['info', 'Alternative: Run "curl -sS https://getcomposer.org/installer | php" to install composer.phar'];
                return back()->withNotify($notify);
            }

            // Change to core directory and run composer install
            $command = 'cd ' . base_path() . ' && ' . $composerPath . ' install --no-dev --optimize-autoloader 2>&1';
            $output = shell_exec($command);

            if ($output === null) {
                $notify[] = ['error', 'Failed to execute composer install command'];
                return back()->withNotify($notify);
            }

            // Clear cache after installation
            Artisan::call('optimize:clear');

            $notify[] = ['success', 'Composer dependencies installed successfully'];
            $notify[] = ['info', 'Output: ' . substr($output, 0, 500) . (strlen($output) > 500 ? '...' : '')];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', 'Composer installation failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function downloadComposerPhar()
    {
        try {
            // Download composer.phar
            $composerPharUrl = 'https://getcomposer.org/composer.phar';
            $composerPharPath = base_path('composer.phar');

            $composerPhar = file_get_contents($composerPharUrl);
            if ($composerPhar === false) {
                $notify[] = ['error', 'Failed to download composer.phar'];
                return back()->withNotify($notify);
            }

            if (file_put_contents($composerPharPath, $composerPhar) === false) {
                $notify[] = ['error', 'Failed to save composer.phar to project root'];
                return back()->withNotify($notify);
            }

            $notify[] = ['success', 'composer.phar downloaded successfully'];
            $notify[] = ['info', 'You can now use the Install Composer Dependencies button'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to download composer.phar: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function manualInstall()
    {
        try {
            $vendorPath = base_path('vendor');

            // Create vendor directory if it doesn't exist
            if (!is_dir($vendorPath)) {
                mkdir($vendorPath, 0755, true);
            }

            // Test if we can write to vendor directory
            $testFile = $vendorPath . '/test_write.tmp';
            if (file_put_contents($testFile, 'test') === false) {
                throw new \Exception('Cannot write to vendor directory. Please check permissions.');
            }
            unlink($testFile);

            // Required packages for DomPDF
            $packages = [
                'barryvdh/laravel-dompdf' => [
                    'url' => 'https://github.com/barryvdh/laravel-dompdf/archive/v3.0.0.zip',
                    'extract_path' => 'barryvdh/laravel-dompdf'
                ],
                'dompdf/dompdf' => [
                    'url' => 'https://github.com/dompdf/dompdf/archive/v2.0.4.zip',
                    'extract_path' => 'dompdf/dompdf'
                ],
                'phenx/php-svg-lib' => [
                    'url' => 'https://github.com/PhenX/php-svg-lib/archive/v0.5.2.zip',
                    'extract_path' => 'phenx/php-svg-lib'
                ],
                'phenx/php-font-lib' => [
                    'url' => 'https://github.com/PhenX/php-font-lib/archive/0.5.6.zip',
                    'extract_path' => 'phenx/php-font-lib'
                ],
                'sabberworm/php-css-parser' => [
                    'url' => 'https://github.com/sabberworm/PHP-CSS-Parser/archive/v8.5.0.zip',
                    'extract_path' => 'sabberworm/php-css-parser'
                ]
            ];

            $installed = [];
            $errors = [];

            foreach ($packages as $package => $info) {
                try {
                    $this->downloadAndExtractPackage($package, $info['url'], $info['extract_path']);
                    $installed[] = $package;
                } catch (\Exception $e) {
                    $errors[] = $package . ': ' . $e->getMessage();
                }
            }

            // Update composer autoloader
            $this->updateAutoloader();

            // Clear cache
            Artisan::call('optimize:clear');

            $notify[] = ['success', 'Manual installation completed'];
            if (!empty($installed)) {
                $notify[] = ['info', 'Installed packages: ' . implode(', ', $installed)];
            }
            if (!empty($errors)) {
                $notify[] = ['warning', 'Failed packages: ' . implode(', ', $errors)];
            }

            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', 'Manual installation failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function directDownload()
    {
        $pageTitle = 'Direct Package Download';
        return view('admin.system.direct-download', compact('pageTitle'));
    }

    public function downloadPackage($package)
    {
        try {
            $packages = [
                'dompdf' => [
                    'url' => 'https://github.com/dompdf/dompdf/archive/v2.0.4.zip',
                    'folder' => 'dompdf-dompdf-2.0.4',
                    'target' => 'dompdf/dompdf'
                ],
                'laravel-dompdf' => [
                    'url' => 'https://github.com/barryvdh/laravel-dompdf/archive/v3.0.0.zip',
                    'folder' => 'laravel-dompdf-3.0.0',
                    'target' => 'barryvdh/laravel-dompdf'
                ],
                'php-svg-lib' => [
                    'url' => 'https://github.com/PhenX/php-svg-lib/archive/v0.5.2.zip',
                    'folder' => 'php-svg-lib-0.5.2',
                    'target' => 'phenx/php-svg-lib'
                ],
                'php-font-lib' => [
                    'url' => 'https://github.com/PhenX/php-font-lib/archive/0.5.6.zip',
                    'folder' => 'php-font-lib-0.5.6',
                    'target' => 'phenx/php-font-lib'
                ],
                'php-css-parser' => [
                    'url' => 'https://github.com/sabberworm/PHP-CSS-Parser/archive/v8.5.0.zip',
                    'folder' => 'PHP-CSS-Parser-8.5.0',
                    'target' => 'sabberworm/php-css-parser'
                ]
            ];

            if (!isset($packages[$package])) {
                $notify[] = ['error', 'Package not found'];
                return back()->withNotify($notify);
            }

            $info = $packages[$package];
            $vendorPath = base_path('vendor');
            $tempFile = $vendorPath . '/temp_' . $package . '.zip';

            // Download the package
            $zipContent = file_get_contents($info['url']);
            if ($zipContent === false) {
                throw new \Exception('Failed to download package');
            }

            // Save to temp file
            if (file_put_contents($tempFile, $zipContent) === false) {
                throw new \Exception('Failed to save package');
            }

            // Extract the zip
            $zip = new \ZipArchive;
            if ($zip->open($tempFile) !== true) {
                throw new \Exception('Failed to open zip file');
            }

            $extractTo = $vendorPath . '/' . $info['target'];
            if (!is_dir($extractTo)) {
                mkdir($extractTo, 0755, true);
            }

            // Extract all files
            $zip->extractTo($extractTo);
            $zip->close();

            // Move files from extracted folder to target
            $extractedFolder = $extractTo . '/' . $info['folder'];
            if (is_dir($extractedFolder)) {
                $this->moveFiles($extractedFolder, $extractTo);
                $this->removeDirectory($extractedFolder);
            }

            // Clean up temp file
            unlink($tempFile);

            // Update autoloader
            $this->updateAutoloader();

            $notify[] = ['success', ucfirst($package) . ' downloaded and installed successfully'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to download ' . $package . ': ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    private function moveFiles($source, $destination)
    {
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;

                if (is_dir($srcPath)) {
                    if (!is_dir($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    $this->moveFiles($srcPath, $destPath);
                } else {
                    rename($srcPath, $destPath);
                }
            }
        }
        closedir($dir);
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function downloadAndExtractPackage($packageName, $url, $extractPath)
    {
        $vendorPath = base_path('vendor');
        $tempFile = $vendorPath . '/temp_' . md5($packageName) . '.zip';
        $extractTo = $vendorPath . '/' . $extractPath;

        try {
            // Download the package
            $zipContent = file_get_contents($url);
            if ($zipContent === false) {
                throw new \Exception('Failed to download package');
            }

            // Save to temp file
            if (file_put_contents($tempFile, $zipContent) === false) {
                throw new \Exception('Failed to save package');
            }

            // Extract the zip
            $zip = new \ZipArchive;
            if ($zip->open($tempFile) !== true) {
                throw new \Exception('Failed to open zip file');
            }

            // Create extract directory
            if (!is_dir($extractTo)) {
                mkdir($extractTo, 0755, true);
            }

            // Extract all files
            $zip->extractTo($extractTo);
            $zip->close();

            // Clean up temp file
            unlink($tempFile);

            return true;

        } catch (\Exception $e) {
            // Clean up on error
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }

    private function updateAutoloader()
    {
        $vendorPath = base_path('vendor');

        // Create basic autoloader files
        $autoloadFiles = [
            'barryvdh/laravel-dompdf/src/' => 'Barryvdh\\DomPDF\\',
            'dompdf/dompdf/src/' => 'Dompdf\\',
            'phenx/php-svg-lib/src/' => 'Svg\\',
            'phenx/php-font-lib/src/' => 'FontLib\\',
            'sabberworm/php-css-parser/src/' => 'Sabberworm\\CSS\\'
        ];

        $psr4 = [];
        foreach ($autoloadFiles as $path => $namespace) {
            $fullPath = $vendorPath . '/' . $path;
            if (is_dir($fullPath)) {
                $psr4[$namespace] = [$fullPath];
            }
        }

        // Create a basic composer autoload_psr4.php file
        $autoloadPsr4Path = $vendorPath . '/composer/autoload_psr4.php';
        if (!is_dir(dirname($autoloadPsr4Path))) {
            mkdir(dirname($autoloadPsr4Path), 0755, true);
        }

        $content = "<?php\n\n\$vendorDir = dirname(__DIR__);\n\$baseDir = dirname(\$vendorDir);\n\nreturn array(\n";
        foreach ($psr4 as $namespace => $paths) {
            $content .= "    '{$namespace}' => array(";
            foreach ($paths as $path) {
                $content .= "\$vendorDir . '/" . str_replace($vendorPath . '/', '', $path) . "',";
            }
            $content = rtrim($content, ',');
            $content .= "),\n";
        }
        $content = rtrim($content, ",\n") . "\n);\n";

        file_put_contents($autoloadPsr4Path, $content);
    }

    public function runMigrations()
    {
        $pageTitle = 'Run Database Migrations';
        return view('admin.system.migrations', compact('pageTitle'));
    }

    public function runMigrationsProcess()
    {
        try {
            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            $output = Artisan::output();

            $notify[] = ['success', 'Migrations completed successfully'];
            if (!empty($output)) {
                $notify[] = ['info', 'Migration output: ' . $output];
            }

            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', 'Migration failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
}
