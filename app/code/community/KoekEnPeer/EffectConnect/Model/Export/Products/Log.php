<?php

    class KoekEnPeer_EffectConnect_Model_Export_Products_Log
    {
        protected $startTime;
        protected $fileLocation;
        protected $fileHandle;

        public function __construct()
        {
            $this->_setStartTime();
        }

        public function create($checkForActiveExport, $exportFolder)
        {
            $this->fileLocation = $exportFolder.'log.txt';
            if (file_exists($this->fileLocation) && $checkForActiveExport)
            {
                $fileContent  = trim(file_get_contents($this->fileLocation));
                $fileLines    = explode(PHP_EOL, substr($fileContent, -2000));
                $fileLastLine = end($fileLines);
                list($logDate, $logValue) = explode(' | ', $fileLastLine, 2);
                $logTimestamp           = strtotime($logDate);
                $logTimestampDifference = time() - $logTimestamp;

                if ($logTimestampDifference < 60)
                {
                    throw new Exception('Previous export still active, last log: '.$logValue.' ('.$logTimestampDifference.' second'.($logTimestampDifference != 1 ? 's' : '').' ago).');
                }
            }

            return true;
        }

        public function update($value)
        {
            $loadTime    = $this->_getLoadTime();
            $memoryUsage = $this->_getMemoryUsage();
            $logLine = '';
            if (!$this->fileHandle)
            {
                if (file_exists($this->fileLocation))
                {
                    if (filesize($this->fileLocation) > 2 * pow(1024, 2))
                    {
                        unlink($this->fileLocation);
                    } else
                    {
                        $logLine .= str_repeat('=', 155).PHP_EOL;
                    }
                }
                $this->fileHandle = fopen($this->fileLocation, 'a');
            }
            $logLine .= date('Y-m-d H:i:s').' | '.str_pad($value, 100, ' ', STR_PAD_RIGHT).' | '.
                        str_pad($loadTime, 9, ' ', STR_PAD_LEFT).' seconds / '.
                        str_pad($memoryUsage, 7, ' ', STR_PAD_LEFT).' MB'.PHP_EOL
            ;

            return fwrite($this->fileHandle, $logLine);
        }

        public function close()
        {
            fclose($this->fileHandle);
        }

        protected function _getStartTime()
        {
            return $this->startTime;
        }

        protected function _setStartTime()
        {
            $this->startTime = microtime(true);

            return true;
        }

        protected function _getLoadTime()
        {
            return round(microtime(true) - $this->_getStartTime(), 3);
        }

        protected function _getMemoryUsage()
        {
            return round(memory_get_usage(true) / pow(1024, 2), 3);
        }
    }
