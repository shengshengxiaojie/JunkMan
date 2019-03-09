<?php
/**
 * Created by PhpStorm.
 * User: Hikki
 * Date: 2018/11/22 0022
 * Time: 上午 11:05
 */

namespace JunkMan\Operation;

use JunkMan\Abstracts\Singleton;
use JunkMan\Configuration\Labour;
use JunkMan\Container\Collector;
use JunkMan\Instrument\Helper;

/**
 * Class OperateStream
 * @package JunkMan\Operation
 */
class OperateStream extends Singleton
{
    /**
     * @var Collector
     */
    private $collector = null;

    /**
     * @param string $title
     * @throws \Exception
     */
    public function start($title = '')
    {
        try {
            $trace_file_info = Helper::multiQuery2Array(debug_backtrace(), ['function' => 'start', 'class' => get_class()]);
            Labour::run($this->collector, $title, $trace_file_info, Collector::TRACE_STREAM);

            xdebug_start_trace(trim($this->collector->getTemp(), Collector::STREAM_SUFFIX));

            set_error_handler(function ($error_no, $error_message, $error_file, $error_line) {
                xdebug_stop_trace();
                $this->collector->setTraceType(Collector::TRACE_ERR);
                $this->collector->setExtend([
                    'error_no' => $error_no,
                    'error_message' => $error_message,
                    'error_file' => $error_file,
                    'error_line' => $error_line
                ]);
                $this->collector->setStatus(Collector::STATUS_END);
                Labour::stop();
                $this->collector->getSENDER()->write($this->collector->message);
                throw new \Exception($error_message);
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function end()
    {
        try {
            xdebug_stop_trace();
            $trace_file_info = Helper::multiQuery2Array(debug_backtrace(), ['function' => 'end', 'class' => get_class()]);
            $trace_to = $trace_file_info['line'];
            $this->collector->setTraceEnd($trace_to);
            $this->collector->setStatus(Collector::STATUS_END);
            Labour::stop();
            $this->collector->getSENDER()->write($this->collector->message);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param null $data
     */
    protected function execute($data = null)
    {
        $this->collector = new Collector();
    }
}