<?php
namespace user1007017\yii\ics;

use DateTime;
use ReflectionClass;
use ReflectionProperty;
use user1007017\yii\ics\ICS_Exception;
/**
 * Class to create an .ics file.
 * forked from jmartinez - some modifications to process multiple VEVENTS
 * and build_footer and build_header VCALENDAR
 */

class ICS {
    const DT_FORMAT = 'Ymd\THis\Z';

    protected $properties = array();
    private $available_properties = array(
        'description',
        'dtend',
        'dtstart',
        'location',
        'summary',
        'url'
    );

    public function __construct($props) {
        $this->set($props);
    }

    public function set($key, $val = false) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            if (in_array($key, $this->available_properties)) {
                $this->properties[$key] = $this->sanitize_val($val, $key);
            }
        }
    }


    public function to_string_arr($rows) {
        return implode("\r\n", $rows);
    }



    public function to_string() {
        $rows = $this->build_props();
        return implode("\r\n", $rows);
    }


    public function build_footer() {

        // Build ICS properties - add footer
        $footer = 'END:VCALENDAR';

        return $footer;
    }



    public function build_header() {
        // Build ICS properties - add header
        $ics_props = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
            'CALSCALE:GREGORIAN',
        );

        return self::to_string_arr($ics_props);
    }



    private function build_props() {
        // Build ICS properties - add header
        $ics_props = array(
            "\r\n".'BEGIN:VEVENT'
        );

        // Build ICS properties - add header
        $props = array();
        foreach($this->properties as $k => $v) {
            $props[strtoupper($k . ($k === 'url' ? ';VALUE=URI' : ''))] = $v;
        }

        // Set some default values
        $props['DTSTAMP'] = $this->format_timestamp('now');
        $props['UID'] = uniqid();

        // Append properties
        foreach ($props as $k => $v) {
            $ics_props[] = "$k:$v";
        }

        // Build ICS properties - add footer
        $ics_props[] = 'END:VEVENT';

        return $ics_props;
    }

    private function sanitize_val($val, $key = false) {
        switch($key) {
            case 'dtend':
            case 'dtstamp':
            case 'dtstart':
                $val = $this->format_timestamp($val);
                break;
            default:
                $val = $this->escape_string($val);
        }

        return $val;
    }

    private function format_timestamp($timestamp) {
        $dt = new DateTime($timestamp);
        return $dt->format(self::DT_FORMAT);
    }

    private function escape_string($str) {
        return preg_replace('/([\,;])/','\\\$1', $str);
    }
}
