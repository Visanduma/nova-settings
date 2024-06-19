<?php

namespace Visanduma\NovaSettings\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Stringable;

class CreateSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-settings:make {name}';

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
            $this->info("Settings : {$path} created");
        } else {
            $this->info("Settings : {$path} already exits");
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
        $namespace = str($this->argument('name'))
            ->whenContains('/', fn (Stringable $string) => $string->beforeLast('/')->prepend('\\'), fn () => '');

        return [
            'NAMESPACE' => 'App\\Nova\\Settings'.$namespace,
            'CLASS_NAME' => str($this->argument('name'))->afterLast('/')->ucfirst(),
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
