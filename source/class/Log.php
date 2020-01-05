<?php

namespace ElBiniou\LivingSource\Recorder;


use Phi\JSONArchive\Archive;

class Log implements \JsonSerializable
{

    private $file;



    /**
     * @var LogEntry[]
     */
    private $entries;

    public function __construct($file)
    {
        $this->file = $file;

        if(is_file($this->file)) {
            $this->load();
        }
    }

    public function load()
    {
        $this->entries = [];
        $data = json_decode(
            file_get_contents($this->file),
            true
        );

        if(isset($data['entries'])) {
            foreach ($data['entries'] as $entry) {
                $entry = new LogEntry($entry['type'], $entry['data'], $entry['time']);
                $this->addEntry($entry);
            }
        }
    }


    public function addEntry(LogEntry $entry)
    {
        $this->entries[] = $entry;
    }


    /**
     * @return $this
     */
    public function save()
    {
        file_put_contents(
            $this->file,
            json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        return $this;
    }

    public function saveSplitted()
    {

    }



    public function jsonSerialize()
    {
        return [
            'entries' => $this->entries
        ];
    }


}

