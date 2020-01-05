<?php

namespace ElBiniou\LivingSource\Recorder;

use ElBiniou\LivingSource\Event;
use ElBiniou\LivingSource\Listener;
use ElBiniou\LivingSource\Source;
use ElBiniou\LivingSource\SourceFileStorage;
use ElBiniou\LivingSource\SourcePathStorage;
use Phi\Console\Command;
use Phi\Console\Option;
use Phi\FileSystem\SplittedFolder;


class PathListener extends Command
{


    /**
     * @var Log
     */
    private $log;


    /**
     * @var PathIndex
     */
    private $pathIndex;

    private $sourcePath;
    private $outputPath;

    private $verbose;



    public function __construct($argv = null, $argc = null)
    {
        parent::__construct($argv, $argc);

        $this->initializeOptions();


        $this->setMain(function ($command) {

            $this->loadOptions();

            if($this->verbose) {
                echo "Starting to listen " . realpath($this->sourcePath) . "\n";
            }

            $listener = new Listener($this->sourcePath);
            $listener->register(array($this, 'listen'));

            $listener->listen();

        });
    }


    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }




    public function listen(Event $event)
    {
        $this->saveLogEntry($event);
        if($event->type !== Listener::EVENT_DELETE) {
            $this->saveNewVersion($event);
        }
    }

    public function saveLogEntry($event)
    {

        if ($event->type === Listener::EVENT_NEW) {
            $entry = new LogEntry('new', [
                'source' => $event->file,
                'livingSource' => $this->getVersionnedFilepath($event->file)
            ]);
            if($this->verbose) {
                echo "NEW\t" . $event->file . "\n";
            }
        }
        else if($event->type === Listener::EVENT_UPDATED) {

            $entry = new LogEntry('edit', [
                'source' => $event->file,
                'livingSource' => $this->getVersionnedFilepath($event->file)
            ]);

            if($this->verbose) {
                echo "MODIFIED\t" . $event->file . "\n";
            }
        }
        else if($event->type === Listener::EVENT_DELETE) {
            $entry = new LogEntry('delete', [
                'source' => $event->file,
                'livingSource' => $this->getVersionnedFilepath($event->file)
            ]);

            if($this->verbose) {
                echo "DELETE\t" . $event->file . "\n";
            }
        }

        $this->log->addEntry($entry);
        $this->log->save();
    }


    private function getVersionnedFilepath($file)
    {
        return  realpath($this->outputPath).'/'.basename($file);
    }

    private function saveNewVersion($event)
    {
        $versionnedFile =$this->getVersionnedFilepath($event->file);

        $source = new Source($event->file);

        $storage = new SourcePathStorage($source, $versionnedFile, true);

        $source->createVersion();
        $storage->save();

        if($this->verbose) {
            echo "\t"."SAVE\tnew version in ".$this->getVersionnedFilepath($event->file) . '.versionned.json'."\n";
        }
    }


    private function initializeOptions()
    {
        $verboseOption = new Option('verbose', false);
        $verboseOption->addAlias('-v');
        $verboseOption->addAlias('--verbose');
        $this->addOption($verboseOption);

        $sourcePathOption = new Option('sourcePath');
        $sourcePathOption->addAlias('-s');
        $sourcePathOption->addAlias('--source');
        $this->addOption($sourcePathOption);


        $outputPathOption = new Option('outputPath');
        $outputPathOption->addAlias('--output');
        $outputPathOption->addAlias('-o');
        $this->addOption($outputPathOption);
    }

    private function loadOptions()
    {

        $this->sourcePath = $this->getOptionValue('sourcePath');
        $this->outputPath = $this->getOptionValue('outputPath');
        $this->verbose = $this->getOptionValue('verbose');

        $this->log = new Log($this->outputPath . '/log.json');
        $this->pathIndex = new PathIndex();

        return $this;
    }
}

