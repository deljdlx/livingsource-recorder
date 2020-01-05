<?php

namespace ElBiniou\LivingSource\Recorder;


class LogEntry implements \JsonSerializable
{

    private $type;
    private $timestamp;

    private $data;

    public function __construct($type, $data, $time = null)
    {
        if($time === null)  {
            $this->timestamp = time();
        }
        else {
            $this->timestamp = $time;
        }

        $this->type = $type;
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return array(
            'time' => $this->timestamp,
            'type' => $this->type,
            'data' => $this->data
        );
    }


}

