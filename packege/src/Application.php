<?php
/**
 * Created by PhpStorm.
 * User: ydtg1
 * Date: 2018/8/19
 * Time: 21:33
 */

/**
 * Class Application
 */
class Application
{
    public function run()
    {
        self::config();
        self::setExamine();
        self::setTraceFile();
        self::setXdebug();
        self::secret();
        self::setTemp();
        self::setSocketHead();
    }

    private static function config()
    {
        $config = file_get_contents(JunkMan::ROOT_PATH . DIRECTORY_SEPARATOR . 'config.json');
        $config = json_decode($config, true);
        Defined::setConfig($config);
    }

    private static function secret()
    {
        $config = Defined::getConfig();
        $secret = Helper::secret($config['app_code'],Defined::getTIME());
        Defined::setSECRET($secret);
    }

    private static function setTemp()
    {
        $path = JunkMan::ROOT_PATH . DIRECTORY_SEPARATOR . 'Temp';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $file = $path . DIRECTORY_SEPARATOR . Defined::getSECRET();
        Defined::setTemp($file);
    }

    private static function setSocketHead()
    {
        $data = [
            'header' => [
                'stream_title' => Defined::getStreamTitle(),
                'time' => Defined::getTIME(),
                'secret' => Defined::getSECRET(),
                'trace_file' => Defined::getTraceFile()
            ]
        ];
        Defined::setSOCKETHEAD($data);
    }

    private static function setExamine()
    {
        date_default_timezone_set('Asia/Shanghai');
        Defined::setTIME(time());
    }

    private static function setXdebug()
    {
        if (!function_exists('xdebug_set_filter')) {
            throw new \Exception('Need to install Xdebug version >= 2.6');
        }
        ini_set('xdebug.collect_params', 4);
        ini_set('xdebug.collect_return', 1);
        ini_set('xdebug.show_mem_delta', 0);
        ini_set('xdebug.collect_assignments', 1);
        ini_set('xdebug.collect_includes', 0);
        ini_set('xdebug.trace_format', 0);
        ini_set('xdebug.profiler_enable', 1);
        ini_set('xdebug.var_display_max_depth', 10);
        ini_set('collect_assignments', 1);
        ini_set('xdebug.coverage_enable', 1);
        xdebug_set_filter(
            XDEBUG_FILTER_TRACING,
            XDEBUG_PATH_BLACKLIST,
            [Defined::getTraceFile()]
        );
    }

    private static function setTraceFile()
    {
        $call_func_data = Helper::multiQuery2Array(debug_backtrace(), ['function' => 'start', 'class' => 'JunkMan']);
        Defined::setTraceFile($call_func_data['file']);
        Defined::setTraceStart($call_func_data['line']);
    }
}