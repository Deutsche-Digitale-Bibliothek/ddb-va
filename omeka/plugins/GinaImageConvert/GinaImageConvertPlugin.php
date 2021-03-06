<?php
/**
 * Image Convert
 *
 * Keeps original names of files and put them in a hierarchical structure.
 *
 * @copyright Copyright Grandgeorg Websolutions 2017
 * @license GPLv3
 * @package ImageConvert
 */

/**
 * The ImageConvert plugin.
 * @package Omeka\Plugins\ImageConvert
 */
class GinaImageConvertPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        // 'before_save_file',
        'after_delete_file',
        'define_routes',
        'config_form',
        'config',
    );

    protected $_filters = array(
        'admin_navigation_main'
    );

    protected static $convertedFiles = array();

    /**
     * @param array $args Args with (File-) Record
     * @return void
     *
     */
    // public function hookBeforeSaveFile($args)
    // {
    //     if (isset($args['record'])
    //         && property_exists($args['record'], 'filename')
    //         && !empty($args{'record'}->filename)
    //         && !$args{'record'}->stored)
    //     {
    //         // $dir = $args{'record'}->getStorage()->getTempDir();
    //         // $file = $dir . '/' . $args{'record'}->filename;
    //         $file = $args{'record'}->getPath('original');
    //         if (isset($file) && !empty($file)) {
    //             $this->validateSize($file, $args);
    //             $this->removeExif($file, false);
    //         }
    //     }
    // }

    /**
     * @param array $args Args with (File-) Record
     * @return void
     *
     */
    public function hookAfterDeleteFile($args)
    {
        if (isset($args['record']) &&
            isset($args['record']->filename) &&
            !empty($args['record']->filename)
        ){
            $path = realpath(FILES_DIR . '/original_compressed');
            $file = $path . '/' . $args['record']->filename;
            if (is_file($file)) {
                unlink($file);
            }

            // webp
            $ext = pathinfo($args['record']->filename, PATHINFO_EXTENSION);
            if ($ext === 'png') {
                $filename = pathinfo($args['record']->filename, PATHINFO_FILENAME);
                $subDirs = array(
                    'fullsize',
                    'middsize',
                    'original_compressed',
                    'square_thumbnails',
                    'thumbnails'
                );
                foreach ($subDirs as $subDir) {
                    $file = FILES_DIR . '/' . $subDir . '/' . $filename . '.webp';
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * @param $file string absolute path to file
     * @param array $args Args with (File-) Record
     * @return void
     */
    public function validateSize($file, $args)
    {
        $helperMaxFileSize = new Omeka_View_Helper_MaxFileSize;
        $record = $args['record'];

        // We assume MB here:
        $maxFileSize = (int) preg_replace('/[^0-9]/', '', $helperMaxFileSize->maxFileSize());
        $maxFileSize = $maxFileSize * 1048576; // 1024 * 1024 = 1048576 | MB to Byte

        $filesize = filesize($file);

        if ($filesize > $maxFileSize) {

            $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

            $msg = __('Die Datei "%s" übersteigt die maximale Dateigrösse von %s.',
                    $record->original_filename, $helperMaxFileSize->maxFileSize())
                . ' '
                . __('Speichervorgang für das gesamte Objekt wurde abgebrochen!');

            unlink($file);

            // The record will not be saved anyway, so skip this:
            // $record->delete();

            $flashMessenger->addMessage($msg, 'error');
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
            $redirector->gotoSimpleAndExit('show', 'items', null, array('id' => $record->item_id));
        }
    }

    /**
     * @param $file string absolute path to file
     * @param $log boolean Log / Debug on or off
     * @return void
     */
    public function removeExif($file, $log = false)
    {
        if (!in_array($file, self::$convertedFiles)) {
            if (is_readable($file)) {
                $allowedExtensions = array('jpg', 'jpeg');
                if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $allowedExtensions)) {

                    if(extension_loaded('imagick')) {
                        $img = new Imagick($file);
                        $profiles = $img->getImageProfiles('icc', true);
                        $img->stripImage();
                        if (isset($profiles) && !empty($profiles) && isset($profiles['icc'])) {
                            $img->profileImage('icc', $profiles['icc']);
                        }
                        $img->writeImage($file);
                        $img->clear();
                        $img->destroy();
                        self::$convertedFiles[] = $file;

                        if ($log === true) {
                            $this->_log($file, 'INFO', ' File successfully converted');
                        }
                    } elseif ($log === true) {
                        $this->_log($file, 'WARNING', 'imagick extension not loaded');
                    }
                } elseif ($log === true) {
                    $this->_log($file, 'WARNING', 'Extension not allowed');
                }
            } elseif ($log === true) {
                $this->_log($file, 'ERROR', 'File is not readable');
            }
        } elseif ($log === true) {
            $this->_log($file, 'INFO', 'File already converted');
        }
    }

    /**
     * @param $file string absolute path to file
     * @param $type string INFO | WARNING | ERROR
     * @param $file msg Log messsage
     * @return void
     */
    protected function _log($file, $type, $msg)
    {
        file_put_contents(__DIR__ . '/log.txt', date('Y-m-d H.i:s') . ' ' . $type . ': ' . $file . ' ' . $msg . "\n", FILE_APPEND);
    }

    /**
     * Add the routes
     *
     * @param Zend_Controller_Router_Rewrite $router
     */
    public function hookDefineRoutes($args)
    {
        // Don't add these routes on the public side to avoid conflicts.
        if (!is_admin_theme()) {
            return;
        }

        $router = $args['router'];

        $router->addRoute(
            'gina-image-convert',
            new Zend_Controller_Router_Route(
                '/gina-image-convert/compress',
                array(
                    'module'     => 'gina-image-convert',
                    'controller' => 'compress',
                    'action'     => 'index',
                    // 'id'         => null
                )
            )
        );

        $router->addRoute(
            'gina-image-convert-showlog',
            new Zend_Controller_Router_Route(
                '/gina-image-convert/showlog',
                array(
                    'module'     => 'gina-image-convert',
                    'controller' => 'compress',
                    'action'     => 'showlog',
                )
            )
        );

        $router->addRoute(
            'gina-image-convert-compressfile',
            new Zend_Controller_Router_Route(
                '/gina-image-convert/compressfile/:id',
                array(
                    'module'     => 'gina-image-convert',
                    'controller' => 'compress',
                    'action'     => 'file',
                    'id'         => null
                )
            )
        );

    }

    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        $params['gina_image_convert'] = $this->getDefaultConfig();
        $options = unserialize(get_option('gina_image_convert'));
        if (isset($options) && !empty($options) && $options !== false) {
            $params['gina_image_convert'] = $this->mergeOptions($params['gina_image_convert'], $options);
        }
        require dirname(__FILE__) . '/config_form.php';
    }

    public function getDefaultConfig()
    {
        return require dirname(__FILE__) . '/default_config.php';
    }

    public function mergeOptions($params, $options)
    {
        $result = array();
        foreach ($params as $sizeKey => $sizeParams) {
            foreach ($sizeParams as $key => $param) {
                if (!isset($options[$sizeKey][$key]) ||
                    (empty($options[$sizeKey][$key]) && $options[$sizeKey][$key] !== 0)
                ) {
                    $result[$sizeKey][$key] = $param;
                } else {
                    $result[$sizeKey][$key] = $options[$sizeKey][$key];
                }
            }
        }
        return $result;
    }

    /**
     * Processes the configuration form.
     *
     * @return void
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        if (isset($post['gina_image_convert']) && !empty($post['gina_image_convert'])) {
            set_option('gina_image_convert', serialize($post['gina_image_convert']));
        }
    }

    /**
     * Modify the admin navigation
     *
     * @param array $navArray The array of navigation links
     * @return array
     */
    public function filterAdminNavigationMain($navArray)
    {
        $currentuser = Zend_Registry::get('bootstrap')->getResource('currentuser');

        if ($currentuser['role'] === 'super') {
            $navArray[] = array(
                'label' => __('Bilder Komprimieren'),
                'uri' => url('gina-image-convert/compress'),
                'visible' => true
            );
        }
        return $navArray;
    }

}
