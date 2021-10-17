<?php

namespace Hwavina\HwaMeta\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class HwaMetaCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'hwa:make:meta';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command make hwa meta tool';

    /**
     * DevMakeCommand constructor.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput() . 'Meta');

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') || !$this->option('force')) && $this->alreadyExists($this->getNameInput() . 'Meta')) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type . ' created successfully.');

        if ($this->option('migration')) {
            $this->createMigration();
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../stubs/hwa_model.stub';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $name = $this->getNameInput() . 'Meta';

        $stub = str_replace([
            '{{ class }}',
            '{{ table }}',
            '{{ relation }}',
            '{{ type }}',
        ], [
            Str::studly(class_basename($name)),
            Str::plural(Str::snake(class_basename($name))),
            Str::snake($this->getNameInput()) . '_id',
            Str::snake($this->getNameInput()),
        ], $stub);
        return $this;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models';
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    private function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->getNameInput() . 'Meta')));

        $this->call('hwa:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        if (str_contains($name, 'meta')) {
            $name = str_replace('meta', '', $name);
        }

        if (str_contains($name, 'Meta')) {
            $name = str_replace('Meta', '', $name);
        }

        return $name;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
        ];
    }
}
