<?php
/**
 * Mustache view renderer for the Yii PHP framework
 *
 *    'viewRenderer' => array(
 *       'class' => 'application.extensions.YiiMustache.YiiMustacheViewRenderer',
 *    )
 *
 * fork from https://github.com/PLoginoff/YiiMustache
 * 
 * @author Pavel "PLoginoff" <ploginoff@gmail.com>
 */
class YiiMustacheViewRenderer extends CApplicationComponent implements IViewRenderer
{
    /**
    * @var string the extension name of the view file. Defaults to '.mustache'.
    */
    public $fileExtension       = '.mustache';
    public $mustachePathAlias   = 'ext.YiiMustache.vendor.Mustache.src.Mustache';
    
    public $mustacheOptions     = array();
    
    private $_m;
    
    public function init()
    {
            // Unregister Yii autoloader
            spl_autoload_unregister(array('YiiBase','autoload'));
 
            // Register Mustache autoloader
            require Yii::getPathOfAlias($this->mustachePathAlias).'/Autoloader.php';
            Mustache_Autoloader::register(Yii::getPathOfAlias($this->mustachePathAlias).DIRECTORY_SEPARATOR.'..');
 
            // Add Yii autoloader again
            spl_autoload_register(array('YiiBase','autoload'));

            $this->_m = new Mustache_Engine(CMap::mergeArray(
                    array(
                        'cache' => Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'Mustache'.DIRECTORY_SEPARATOR.'cache',
                        'partials_loader' => new Mustache_Loader_FilesystemLoader($this->getTemplatesPath(),
                            array('extension' => $this->fileExtension)),
                        'escape' => function($value) {
                            return CHtml::encode($value);
                        },
                        'charset' => Yii::app()->charset,
                    ),$this->mustacheOptions)
            );
    }

    private function getTemplatesPath()
    {
        if ( Yii::app()->getTheme() !== null ) {
            $viewPath = Yii::app()->getTheme()->getViewPath();
        } else {
            $viewPath = Yii::app()->getController()->getViewPath();
        }
        return $viewPath;
    }
    
    /**
    * Renders a view file.
    * This method is required by {@link IViewRenderer}.
    * @param CBaseController $context the controller or widget who is rendering the view file.
    * @param string $sourceFile the view file path
    * @param mixed $data the data to be passed to the view
    * @param boolean $return whether the rendering result should be returned
    * @return mixed the rendering result, or null if the rendering result is not needed.
    */
    public function renderFile($context,$sourceFile,$data,$return)
    {
            if(!is_file($sourceFile) || ($file=realpath($sourceFile))===false) {
                    throw new CException(Yii::t('yii','View file "{file}" does not exist.',array('{file}'=>$sourceFile)));
            }

            $rendered=$this->_m->render(file_get_contents($sourceFile),$data);
            
            if ( $return ) {
                return $rendered;
            } else {
                echo $rendered;
            }
    }
}
