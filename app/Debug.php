<?php

class Debug{
    public static function print_log($var, $exit = false, $in_file = false, $filename = false){
        //собираем полную директорию для логов
        $full_log_dir = dirname(dirname(__FILE__)) . '/storage/logs/';

        if($in_file) ob_start();

        if (!$in_file) echo '<pre>';
        print_r($var);
        if (!$in_file) echo '</pre>' . "\n";
        if ($in_file) $content = ob_get_contents();

        if(empty($filename)) {
            $filename='print_log.log';
        } else {
            $filename=$filename.".log";
        }
        if ($in_file) {
            $file = fopen($full_log_dir . $filename, "a+");
            fwrite($file, "\n\n******************************\n");
            fwrite($file, date("Y-m-d H:i:s") . "\n");
            fwrite($file, $content);
            fclose($file);
            empty($file);

            ob_end_clean();
        }

        if ($exit) exit;
    }
}