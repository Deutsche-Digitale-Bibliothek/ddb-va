<?php

class Compressor
{
    public $slug = '';
    public $basePath = '';
    public $stateFile = '';
    public $logFile = '';
    public $options = null;
    public $dirs = array();
    public $log = array();
    public $maxQuality = 90;

    public function __construct($slug = '')
    {
        $this->slug = $slug;
        $this->setBasePath();
        $this->setStateFile();
        $this->setLogFile();
        $this->setOptions();
        $this->setDirs();
    }

    public function setBasePath()
    {
        $this->basePath = realpath(
            __DIR__ .
            '/../../../../public/' .
            $this->slug .
            '/files'
        );
    }

    public function setStateFile()
    {
        $this->stateFile = realpath(
            $this->basePath .
            DIRECTORY_SEPARATOR .
            'compress_state.txt'
        );
    }

    public function setLogFile()
    {
        $this->logFile = realpath(
            $this->basePath .
            DIRECTORY_SEPARATOR .
            'compress.log'
        );
    }

    public function setOptions()
    {
        $this->options = json_decode(
            file_get_contents($this->logFile),
            true
        );
    }

    public function setDirs()
    {
        $basePath = $this->basePath . DIRECTORY_SEPARATOR;
        $this->dirs = array(
            'original' => $basePath . 'original',
            'original_compressed' => $basePath . 'original_compressed',
            'fullsize' => $basePath . 'fullsize',
            'middsize' => $basePath . 'middsize',
            'square_thumbnails' => $basePath . 'square_thumbnails',
            'thumbnails' => $basePath . 'thumbnails',
        );
    }

    public function main()
    {
        $this->checkOriginalCompressedDir();
        $this->compress('original', 'original_compressed', true);
        $this->compressSized('fullsize', 1920, 1080);
        $this->compressSized('middsize', 960, 960);
        $this->compressSized('thumbnails', 360, 360);
        $this->compressSized('square_thumbnails', 360, 360, true);
        file_put_contents($this->stateFile, 'off');
    }

    public function checkOriginalCompressedDir()
    {
        if (!is_dir($this->dirs['original_compressed'])) {
            mkdir($this->dirs['original_compressed'], 0755);
        }
    }

    public function getRecompressCommand($in, $out)
    {
        // Do not use --strip option, as it will remove ICC profiles'
        return 'jpeg-recompress'
        . ' --target '  . $this->options['params']['compressall_target']
        . ' --min '     . $this->options['params']['compressall_min']
        . ' --max '     . $this->options['params']['compressall_max']
        . ' --loops '   . $this->options['params']['compressall_loops']
        . ' --accurate '
        . $in
        . ' '
        . $out
        . ' 2>&1';
    }

    public function compress($intype, $outtype, $log)
    {
        $ext = array('jpg', 'jpeg');
        $iterator = new DirectoryIterator($this->dirs[$intype]);
        foreach ($iterator as $entry) {
            if ($entry->isFile() &&
                in_array(
                    strtolower(
                        pathinfo($entry->getFilename(), PATHINFO_EXTENSION)
                    ),
                    $ext
                )
            ) {
                $outfile = $this->dirs[$outtype]
                    . DIRECTORY_SEPARATOR
                    . $entry->getFilename();

                $recompress = $this->getRecompressCommand($entry->getPathname(), $outfile);
                $output = array();
                $retval = false;
                exec($recompress, $output, $retval);

                if ($log) {
                    $this->log[] = array(
                        'file' => $entry->getFilename(),
                        'time' => date('Y.m.d. H:i:s'),
                        'error' => $retval,
                        'compress' => $output

                    );
                }
            }
        }
        if ($log) {
            $this->writeLogfile();
        }
    }

    public function compressSized($type, $width, $height, $crop = false)
    {
        $ext = array('jpg', 'jpeg');
        $extadd = array('png', 'gif');
        $iterator = new DirectoryIterator($this->dirs['original']);
        foreach ($iterator as $entry) {

            $fileExtension = strtolower(pathinfo($entry->getFilename(), PATHINFO_EXTENSION));
            $filename = pathinfo($entry->getFilename(), PATHINFO_FILENAME);

            if (
                ($entry->isFile() && in_array($fileExtension, $ext)) ||
                ($entry->isFile() && in_array($fileExtension, $extadd))
            ) {

                $this->resizeImage(
                    $this->dirs['original'],
                    $this->dirs[$type],
                    $entry->getFilename(),
                    $filename,
                    $width,
                    $height,
                    $crop
                );
                $file = $this->dirs[$type]
                    . DIRECTORY_SEPARATOR
                    . $filename . '.jpg';

                $recompress = $recompress = $this->getRecompressCommand($file, $file);
                $output = array();
                $retval = false;
                exec($recompress, $output, $retval);

            }
        }
        // $this->writeLogfile();
    }

    public function resizeImage($srcDir, $outDir, $file, $filename, $width, $height, $crop = false)
    {
        if(extension_loaded('imagick')) {
            $img = new Imagick($srcDir . DIRECTORY_SEPARATOR . $file);

            // removeExif
            $profiles = $img->getImageProfiles('icc', true);
            $img->stripImage();
            if (isset($profiles) && !empty($profiles) && isset($profiles['icc'])) {
                $img->profileImage('icc', $profiles['icc']);
            }

            // make max Quality selectable?
            $quality = $img->getImageCompressionQuality();
            // echo $file . ' - ' . $quality . "\n";
            if ($quality > $this->maxQuality) {
                $quality = $this->maxQuality;
            }

            if ($crop === true) {
                $img->cropThumbnailImage($width, $height);
            } else {
                // imagick::FILTER_LANCZOS, slow but good ...
                $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
            }

            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality($quality);

            $img->writeImage($outDir . DIRECTORY_SEPARATOR . $filename . '.jpg');
            $img->clear();
            $img->destroy();
        }
    }

    public function writeLogfile()
    {
        $log = array(
            'start' => $this->options['start'],
            'end' => date('Y.m.d. H:i:s'),
            'params' => $this->options['params'],
            'files' => $this->log
        );
        file_put_contents($this->logFile, json_encode($log));
    }
}

$shortopts = "s:";
$longopts  = array("slug:");
$options = getopt($shortopts, $longopts);
$slug = null;
if (isset($options['slug'])) {
    $slug = $options['slug'];
} elseif (isset($options['s'])) {
    $slug = $options['s'];
}
if (!isset($slug)) {
    exit(1);
} else {
    $compressor = new Compressor($slug);
    $compressor->main();
}
?>