<?php

namespace Visanduma\NovaProfile\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nonfig:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new settings class';

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (! $this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exits");
        }

        return Command::SUCCESS;
    }

    public function getSourceFilePath()
    {
        return app_path('Nova/Settings').'/'.$this->getSingularClassName($this->argument('name')).'.php';
    }

    public function getStubPath()
    {
        return __DIR__.'/../../../resources/stubs/user_settings.stub';
    }

    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    public function getSingularClassName($name)
    {
        return str($name)->ucfirst();
    }

    public function getStubVariables()
    {
        return [
            'NAMESPACE' => 'App\\NovaSettings',
            'CLASS_NAME' => $this->argument('name'),
        ];
    }

    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$'.$search.'$', $replace, $contents);
        }

        return $contents;

    }

    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
