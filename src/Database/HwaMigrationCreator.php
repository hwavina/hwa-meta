<?php

namespace Hwavina\HwaMeta\Database;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class HwaMigrationCreator extends MigrationCreator
{
    /**
     * The custom app stubs directory.
     *
     * @var string
     */
    protected $customStubPath;

    public function __construct(Filesystem $files, $customStubPath = null)
    {
        parent::__construct($files, $customStubPath);
    }

    /**
     * Get the migration stub file.
     *
     * @param $table
     * @param $create
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub($table, $create)
    {
        $stub = __DIR__ . '/../../stubs/hwa_migration_meta.stub';
        return $this->files->get($stub);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  $name
     * @param  $stub
     * @param  $table
     * @return string
     */
    protected function populateStub($name, $stub, $table)
    {
        return str_replace(
            [
                '{{ class }}',
                '{{ table }}',
                '{{ relation }}',
                '{{ relation_table }}',
            ],
            [
                $this->getClassName($name),
                Str::plural(Str::snake(class_basename($table))),
                Str::replace('_metas', '', Str::plural(Str::snake(class_basename($table)))) . '_id',
                Str::replace('_metas', '', Str::plural(Str::snake(class_basename($table)))) . 's'
            ],
            $stub
        );
    }
}
