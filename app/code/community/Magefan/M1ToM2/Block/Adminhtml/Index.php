<?php  

class Magefan_M1ToM2_Block_Adminhtml_Index extends Mage_Adminhtml_Block_Template
{
    const RESPONSE_TYPE_ERROR = 'error';
    const RESPONSE_TYPE_SUCCESS = 'success';

    public function getMagentoVersion()
    {
        return trim(implode('.', Mage::getVersionInfo()), '.');
    }

    public function getAllExtensions()
    {
        return(array)Mage::getConfig()->getNode('modules')->children();
    }

    public function getModelSizes()
    {
        $models = array(
            'sales/order',
            'sales/order_invoice',
            'sales/order_shipment',
            'sales/order_creditmemo',
            'catalog/product',
            'catalog/category',
            'customer/customer',
            'customer/group',
            'catalogrule/rule',
            'salesrule/rule',
            'newsletter/subscriber',
            'cms/page',
            'cms/block',
            'core/website',
            'core/store_group',
            'core/store',
        );

        $sizes = array();
        foreach ($models as $model) {
             $sizes[$model] = Mage::getModel($model)->getCollection()->getSize();
        }

        return $sizes;
    }

    public function getThemes()
    {
        $themes = array();
        foreach (Mage::app()->getStores() as $store) {
            $package = Mage::getStoreConfig('design/package/name', $store->getId());
            $template = Mage::getStoreConfig('design/theme/template', $store->getId());
            if (!$template) {
                $template = 'default';
            }
            $themes[] = $package . '/' . $template;
        }

        return array_unique($themes);
    }

    public function getOverridenFilesInLocal()
    {
        $localCodePool = '/app/code/local';
        $otherCodePools = array(
            '/app/code/community',
            '/app/code/core',
            '/lib'
        );

        $result = array();

        $localFolder = BP . $localCodePool;
        if (is_dir($localFolder)) {
            $localFiles = $this->getFolderFiles($localFolder);
            foreach ($localFiles as $localFile) {
                foreach ($otherCodePools as $otherCodePool) {
                    $otherFile = str_replace($localCodePool, $otherCodePool, $localFile);
                    if (file_exists($otherFile)) {
                        $result[str_replace(BP, '', $otherFile)] = str_replace(BP, '', $localFile);
                    }
                }

            }
        }

        return $result;
    }

    protected function getFolderFiles($path)
    {
        $return_array = array();
        $dir = opendir($path);
        while (($file = readdir($dir)) !== false) {
            if ($file[0] == '.') continue;
            $fullpath = $path . '/' . $file;
            if (is_dir($fullpath)) {
                $return_array = array_merge($return_array, $this->getFolderFiles($fullpath));
            } else {
                $return_array[] = $fullpath;
            }
        }
        return $return_array;
    }

    public function checkMysqlVersion()
    {
        try {
            $required = '5.6';
            preg_match('/[0-9]\.[0-9]+\.[0-9]+/', shell_exec('mysql -V'), $current);
            $current = $current[0];

        } catch (\Exception $e) {
            return array(
                'responseType' => self::RESPONSE_TYPE_ERROR,
                'data' => array(
                    'error' => 'phpExtensionError',
                    'message' => 'Cannot determine current MySQL version: ' . $e->getMessage()
                ),
            );
        }

        if (version_compare($required, $current) > 0) {
            return array(
                'responseType' => self::RESPONSE_TYPE_ERROR,
                'data' => array(
                    'error' => 'phpExtensionError',
                    'message' => 'MySQL v' . $current . ' in used but at least v' . $required . ' is required'
                ),
            );
        } else {
            return array(
                'responseType' => self::RESPONSE_TYPE_SUCCESS,
                'data' => array(
                    'message' => 'MySQL v' . $current .' is compatible with Magento 2.'
                ),
            ); 
        }
    }


    public function checkPhpVersion()
    {
        try {
            $required = '7.0.2';
            $current = explode('-', PHP_VERSION);
            $current = $current[0];

        } catch (\Exception $e) {
            return array(
                'responseType' => self::RESPONSE_TYPE_ERROR,
                'data' => array(
                    'error' => 'phpExtensionError',
                    'message' => 'Cannot determine current PHP version: ' . $e->getMessage()
                ),
            );
        }

        if (version_compare($required, $current) > 0) {
            return array(
                'responseType' => self::RESPONSE_TYPE_ERROR,
                'data' => array(
                    'error' => 'phpExtensionError',
                    'message' => 'PHP v' . $current . ' in used but at least v' . $required . ' is required'
                ),
            );
        } else {
            return array(
                'responseType' => self::RESPONSE_TYPE_SUCCESS,
                'data' => array(
                    'message' => 'PHP v'.$current.' is compatible with Magento 2.'
                ),
            ); 
        }
    }


    public function checkPhpExtensions()
    {
        try {
            $required = $this->getRequiredExtensions();
            $current = $this->getCurrentExtensions();
        } catch (\Exception $e) {
            return array(
                'responseType' => self::RESPONSE_TYPE_ERROR,
                'data' => array(
                    'error' => 'phpExtensionError',
                    'message' => 'Cannot determine required PHP extensions: ' . $e->getMessage()
                ),
            );
        }
        $responseType = self::RESPONSE_TYPE_SUCCESS;
        $missing = array_values(array_diff($required, $current));
        if ($missing) {
            $responseType = self::RESPONSE_TYPE_ERROR;
        }
        return array(
            'responseType' => $responseType,
            'data' => array(
                'required' => $required,
                'missing' => $missing,
            ),
        );
    }


    protected function getCurrentExtensions()
    {
        $extensions = get_loaded_extensions();
        foreach ($extensions as $k => $ext) {
            $extensions[$k] = str_replace(' ', '-', strtolower($ext));
        }

        return $extensions;
    }


    protected function getRequiredExtensions()
    {
        return array(
            0 => 'curl',
            1 => 'dom',
            2 => 'hash',
            3 => 'openssl',
            4 => 'xmlwriter',
            6 => 'pcre',
            9 => 'gd',
            11 => 'iconv',
            12 => 'mcrypt',
            14 => 'simplexml',
            15 => 'spl',
            16 => 'xsl',
            17 => 'intl',
            18 => 'mbstring',
            19 => 'ctype',
            32 => 'pdo_mysql',
            34 => 'soap',
            37 => 'zip',
            39 => 'tokenizer',
            42 => 'phar',
            44 => 'libxml',
        );
    }





    public function getOverridenFilesViaXML()
    {
        $rewritesArray = array();

        foreach($this->getTypes() as $type => $label) {
            $rewrites = $this->_collectRewrites($type);
            foreach($rewrites as $initialClass => $rewritesData) {
                $rewriteItem = new Varien_Object();
                $rewriteItem->setClass($initialClass);                
                $rewriteItem->setType($type);
                $rewriteItem->setRewrites($rewritesData);                
                $rewriteItem->setConflict($rewritesData['classes']);

                $rewritesArray[] = $rewriteItem;
            }
        }

        return $rewritesArray;
    }

    public function getTypes()
    {
        $types = array(
            'helper' => 'Helper',
            'block'  => 'Block',
            'model'  => 'Model',
        );

        return $types;
    }

    protected function _collectRewrites($type)
    {
        $modelsRewrites = array();
        
        $modules = Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $modName=>$module) {
            if (!$module->is('active')) {
                continue;
            }
            
            $configFile = Mage::getConfig()->getModuleDir('etc', $modName) . DS . 'config.xml';
            $moduleConfig = Mage::getModel('core/config_base');
            $moduleConfig->loadString('<config/>');
            $moduleConfigBase = Mage::getModel('core/config_base');
            $moduleConfigBase->loadFile($configFile);
            $moduleConfig->extend($moduleConfigBase, true);

            $typeNode = $moduleConfig->getNode()->global->{$type.'s'};
            
            if (!$typeNode) {
                continue;
            }
   
            $nodes = $typeNode->children();
            foreach($nodes as $nodeName => $config) {
                $rewrites = $config->rewrite;
            
                if ($rewrites) {
                    $classes = array();
                    foreach($rewrites->children() as $classId => $newClass) {
                        $classes[] = (string)$newClass;
                        $initialClass = $this->_getClassName($type, $nodeName, $classId);
                        $usedRewrite = $this->_getClassName($type, $nodeName, $classId, true);
                        
                        $color = false;
                        
                        if ($newClass == $usedRewrite) {
                            $conflict = 0;
                        } elseif (is_subclass_of($usedRewrite, $newClass)) {
                            $conflict = 1;
                        } else {
                            $conflict = 2;
                        }
                        
                        $modelsRewrites[$initialClass]['classes'][] = array(
                            'class'    => (string)$newClass,
                            'color'    => $color,
                            'conflict' => $conflict,
                        );
                        if (!isset($modelsRewrites[$initialClass]['filter_condition'])) {
                            $modelsRewrites[$initialClass]['filter_condition'] = (string)$newClass . ' ';
                        } else {
                            $modelsRewrites[$initialClass]['filter_condition'] .= (string)$newClass . ' ';
                        }
                    }
                }
            }
        }

        return $modelsRewrites;
    }
    
    protected function _getClassName($type, $groupId, $classId, $rewrites = false)
    {
        $config = Mage::getConfig()->getNode()->global->{$type.'s'}->{$groupId};        
        
        if ($rewrites && isset($config->rewrite->$classId)) {
            $className = (string)$config->rewrite->$classId;
        } else {        
            if (!empty($config)) {
                $className = $config->getClassName();
            }
            if (empty($className)) {
                $className = 'mage_'.$groupId.'_'.$type;
            }
            if (!empty($classId)) {
                $className .= '_'.$classId;
            }
            $className = uc_words($className);    
        }
    
        return $className;
    }


}