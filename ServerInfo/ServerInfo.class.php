<?php
class ServerInfo
{

    public $info;

    function __construct()
    {


        $arraydata = [];

        $this->info = new stdClass();

        
        $this->info->name = "OSversion";
        $this->info->value = shell_exec('lsb_release -sr');;
        $this->info->display = "Ubuntu";

        $arraydata[] = $this->info;
        

        $this->info = new stdClass();

        
        $this->info->name = "phpversion";
        $this->info->value = explode("-",PHP_VERSION)[0];
        $this->info->display = "PHP";

        $arraydata[] = $this->info;

        $output = shell_exec('mysql -V');
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
        

        $this->info = new stdClass();
        $this->info->name = "mysqlversion";
        $this->info->value = $version[0];
        $this->info->display = "MySql";
        $arraydata[] = $this->info;

        $fh = fopen('/proc/meminfo', 'r');
        $mem = 0;

        while ($line = fgets($fh)) {
            $pieces = array();

            if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                //$this->info->MemTotal = $pieces[1];


                $this->info = new stdClass();
                $this->info->name = "MemTotal";
                $this->info->value = $pieces[1];
                $this->info->display = "Всего памяти";
                $arraydata[] = $this->info;
            }
            if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
                //$this->info->MemFree = $pieces[1];


                $this->info = new stdClass();
                $this->info->name = "MemFree";
                $this->info->value = $pieces[1];
                $this->info->display = "Свободной памяти";
                $arraydata[] = $this->info;
            }

            if (preg_match('/^MemAvailable:\s+(\d+)\skB$/', $line, $pieces)) {
                //        $this->info->MemAvailable = $pieces[1];

                $this->info = new stdClass();
                $this->info->name = "MemAvailable";
                $this->info->value = $pieces[1];
                $this->info->display = "Занято памяти";
                $arraydata[] = $this->info;
            }
        }
        fclose($fh);

        /////////////////////////////////////


        //$this->info->FreeDiskSpace=disk_free_space("/");
        $this->info = new stdClass();
        $this->info->name = "FreeDiskSpace";
        $this->info->value = disk_free_space("/");;
        $this->info->display = "Свободно на диске";
        $arraydata[] = $this->info;


        //$this->info->TotalDiskSpace=disk_total_space("/");

        $this->info = new stdClass();
        $this->info->name = "TotalDiskSpace";
        $this->info->value = disk_total_space("/");;
        $this->info->display = "Всего на диске";
        $arraydata[] = $this->info;
        $this->info = $arraydata;




        $procd = shell_exec("cat /proc/stat | grep '^cpu '");
        $cpu_arr = explode(' ', trim(str_replace('cpu ', '', $procd)));
        $idle = $cpu_arr[3];
         
        if (is_file('/tmp/cpu_proc')) {
            $prev_procd = explode(' ', file_get_contents('/tmp/cpu_proc'));
            $prev_idle = $prev_procd[0];
            $prev_total = $prev_procd[1];
        } else {
            $prev_idle = 0;
            $prev_total = 0;
        }
         
        $total = array_sum($cpu_arr);
        $diff_idle = $idle - $prev_idle;
        $diff_total = $total - $prev_total;
        $diff_usage = (1000 * ($diff_total - $diff_idle) / $diff_total + 5) / 10;
         
        file_put_contents('/tmp/cpu_proc', $idle.' '.$total);
         
     

     $this->info = new stdClass();
     $this->info->name = "Processor";
     $this->info->value = $diff_usage;;
     $this->info->display = "Процессор";
     $arraydata[] = $this->info;
     $this->info = $arraydata;

    }
}
