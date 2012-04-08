<?php
namespace Utopia\Components\Template;

use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Cache\Cache;
use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;
use Utopia\Components\Logger\Logger;

class Template extends ComponentRoot
{
    private $_controller;        //BaseContoller


    public static function isSingleton() {
        return false;
    }

    public function initialize($mixed=false){
        ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);
    }

    /**
     * set controll to render from
     *
     * @param $controller
     *
     * @return $this;
     */
    public function setController($controller) {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * render pages using native php
     *
     * @param $array inputs
     */
    public function render($jscss=true) {

        $html = '';
        $template_filename = $this->_controller->getTemplate();
        if (false == $template_filename
            || false == $string = file_get_contents($template_filename)
        ){
            Logger::summon()->log("template '".$this->_controller->getTemplate(false)."' cannot be read", ComponentRoot::LEVEL_ERROR);
            return $html;
        }

        //fetch from cache
        $cache = Cache::summon();
        $key = md5($string);
        $f = ComponentRoot::ERROR;
        if (true == $cache->isEnabled()){
            $f = Cache::summon()->get($key);
            if (file_exists($f)){
                Logger::summon()->log("cache loaded '$f'", ComponentRoot::LEVEL_TRIVAL);
            } else {
                $f = ComponentRoot::ERROR;
            }
        }

        if (ComponentRoot::ERROR === $f){
            //generate string
            $template_string = $this->buildTemplate($string, $jscss);

            //writing to file
            $f = '/tmp/'.md5($template_string).'.template';
            $fp = @fopen($f, 'w');
            if ($fp) {
                fwrite($fp, $template_string);
                fclose($fp);
            } else {
                Logger::summon()->log("cannot open file '$f' for writing", ComponentRoot::LEVEL_WARNING);
            }

            //save to cache
            if (true == $cache->isEnabled()
                && false == $cache->exists($key)
            ){
                $ttl = ConfigurationBundle::summon()->{'template>cache>ttl'};
                $cache->add($key, $f, $ttl);
            }
        }

        //loading file
        if (file_exists($f)) {
            Logger::summon()->log("loading template from '$f'", ComponentRoot::LEVEL_TRIVAL);
            ob_start();
            include($f);
            $html = ob_get_clean();
        } else {
            Logger::summon()->log("cannot find file '$f' for rendering", ComponentRoot::LEVEL_ERROR);
        }
        return $html;
    }

    public function buildTemplate($string, $jscss){
        //container to store js/css of the module included in the template
        $alljs = array();
        $allcss = array();

        /*=======some template-specific rules========
         * replace {{$xxx}} to <?php echo $xxx; ?>
         */
        $checks = array(
            'mod'      => '/\{\s*mod:([^\}^\n]*)\s*\}/',
            'css'	   => '/\{\s*css\s*\}/',
            'js'       => '/\{\s*js\s*\}/',
            'meta'	   => '/\{\s*meta\s*\}/',
            'debug'    => '/\{\s*debug\s*\}/',
            'timer'    => '/\{\s*timer\s*\}/',
        	'apis'     => '/\{\s*(apis>[^\}^\n]*)\s*\}/',
            'modules'  => '/\{\s*(modules>[^\}^\n]*)\s*\}/',
            'args'     => '/\{\s*(args>[^\}^\n]*)\s*\}/',
        	'params'   => '/\{\s*(params>[^\}^\n]*)\s*\}/',
        	'outputs'  => '/\{\s*(outputs>[^\}^\n]*)\s*\}/',
            'php'      => '/\{\s*\$([^\}^\n]*)\s*\}/',
            'word'     => '/\{\s*([^\}^\n]*)\s*\}/',    //must be the last item
        );
        foreach ($checks as $id => $check) {
            if (preg_match($check, $string) > 0) {
                switch ($id) {
                    case 'css':
                        if ($jscss) {
                            $hostname = ConfigurationBundle::summon()->{'generic>hostname'};
                            $csses = array_unique(array_merge($this->_controller->getCss(), $allcss));
                            if ("dev" == ConfigurationBundle::summon()->get_env('environment')) {
                                $css_tags = array();
                                foreach($csses as $css){
                                    //OLD implementation
                                    //$css = str_replace('\/\/', '//', $css);
                                    //$css_tags[] = "<link rel=\"stylesheet\" href=\"$css\" type=\"text/css\" media=\"screen\">";
                                    $css_tags[] = "<link rel=\"stylesheet\" href=\"$hostname/css/?f=$css\" type=\"text/css\" media=\"screen\">";
                                }
                                $string = preg_replace($check, implode("\n", $css_tags), $string);
                            } else {
                                $css_tag = "<link rel=\"stylesheet\" href=\"$hostname/css/?f=". implode(",", $csses) ."\" type=\"text/css\" media=\"screen\">";
                                $string = preg_replace($check, $css_tag, $string);
                            }
                        } else {
                            $string = preg_replace($check, '', $string);    //remove all related tags
                        }
                        break;
                    case 'js':
                        if ($jscss) {
                            $hostname = ConfigurationBundle::summon()->{'generic>hostname'};
                            $jses = array_unique(array_merge($this->_controller->getJs(), $alljs));
                            if ("dev" == ConfigurationBundle::summon()->get_env('environment')) {
                                $js_tags = array();
                                foreach($jses as $js){
                                    $js = str_replace('\/\/', '//', $js);
                                    $js_tags[] = "<script type=\"text/javascript\" src=\"$hostname/js/?f=$js\"></script>";
                                }
                                $string = preg_replace($check, implode("\n", $js_tags), $string);
                            } else {
                                $js_tag = "<script type=\"text/javascript\" src=\"$hostname/js/?f=". implode(",", $jses) ."\"></script>";
                                $string = preg_replace($check, $js_tag, $string);
                            }
                        } else {
                            $string = preg_replace($check, '', $string);    //remove all related tags
                        }
                        break;
                    case 'meta':
                        if ($jscss) {
                            $string = preg_replace($check, '<meta>', $string);
                        } else {
                            $string = preg_replace($check, '', $string);    //remove all related tags
                        }
                        break;
                    case 'apis':
                    case 'modules':
                    case 'args':
                    case 'params':
                    case 'outputs':
                        while (1 == preg_match($check, $string, $matches, PREG_OFFSET_CAPTURE)){
                            if (false !== ($end_tag_pos = strpos($string, '?>', $matches[0][1]))
                                && $end_tag_pos < ($start_tag_pos = strpos($string, '<?php', $matches[0][1]))
                            ){
                                $string = preg_replace($check, '$this->_controller->getData(\'$1\')', $string, 1);
                            } else {
                                $string = preg_replace($check, '<?php $this->displayVariable($this->_controller->getData(\'$1\')); ?>', $string, 1);
                            }
                        }
                    case 'php':
                        while (1 == preg_match($check, $string, $matches, PREG_OFFSET_CAPTURE)){
                            //do not add php open close tags, treat like normal php valuable
                            if (false !== ($end_tag_pos = strpos($string, '?>', $matches[0][1]))
                                && $end_tag_pos < ($start_tag_pos = strpos($string, '<?', $matches[0][1]))
                            ){
                                $string = preg_replace($check, '\$$1', $string, 1);
                            } else {
                                $string = preg_replace($check, '<?php $this->displayVariable(\$$1); ?>', $string, 1);
                            }
                        }
                        break;
                    case 'word':
                        while (1 == preg_match($check, $string, $matches, PREG_OFFSET_CAPTURE)){
                            //do not add php open close tags, treat like normal php valuable
                            if (false !== ($end_tag_pos = strpos($string, '?>', $matches[0][1]))
                                && $end_tag_pos < ($start_tag_pos = strpos($string, '<?php', $matches[0][1]))
                            ){
                                $string = preg_replace($check, '\'$1\'', $string, 1);
                            } else {
                                $string = preg_replace($check, '<?php $this->displayVariable(\'$1\'); ?>', $string, 1);
                            }
                        }
                        break;
                    case 'mod':
                        preg_match_all($check, $string, $matches);
                        foreach($matches[0] as $key => $match) {
                            $module = $this->_controller->getModule($matches[1][$key]);
                            if ($module instanceof BaseWorkflow){
                                if ($module->getController() == $this->_controller) {
                                    $string = str_replace($matches[0][$key], '', $string);
                                    Logger::summon()->log('potential looping case at Template, module rendering terminated', ComponentRoot::LEVEL_ERROR);
                                } else {
                                    $module_return = $module->render(array(
                                        'jscss'=>false
                                    ));
                                    //get its js/css for later use
                                    $allcss = array_merge($allcss, $module->getController()->getCss());
                                    $alljs = array_merge($alljs, $module->getController()->getJs());

                                    $module_str = is_array($module_return)? print_r($module_return,true): $module_return;
                                    $string = str_replace($matches[0][$key], "<div id=\"mod_{$matches[1][$key]}\">\n{$module_str}\n</div>", $string);
                                }
                            } else {
                                $string = str_replace($matches[0][$key], '', $string);
                                Logger::summon()->log("module '{$matches[1][$key]}' not found", ComponentRoot::LEVEL_WARNING);
                            }
                        }
                        break;
                    case 'debug':
                        $debug_str = "dev" == ConfigurationBundle::summon()->get_env('environment')
                            ? "\n<pre class=\"debug\">".implode("<br/>\n",Logger::summon()->getResponses('html'))."</pre>"
                            : "";
                        $string = preg_replace($check, $debug_str, $string);
                        break;
                    case 'timer':
                        GLOBAL $TIMER;
                        $elapsed = microtime(true) - $TIMER;
                        $string = preg_replace($check, "<!-- elapsed: $elapsed -->", $string);
                        break;
                }
            }
        }
        return $string;
    }

    /**
     * display variables depends on what it is
     * TODO: use this to handle translation as well
     *
     * @param $var
     */
    protected function displayVariable($var) {
        //FIXME: this function cannot accept any local variables in tpl, but only $d vars

        switch (gettype($var)) {
            case "boolean": $var==true? "TRUE":"FALSE"; break;
            case "integer":
            case "double":
            case "string": echo $var; break;
            case "array":
            case "object":
            case "resource": var_dump($var); break;
            case "NULL":
            case "unknown type":
            default:
                //Logger::summon()->log("var $var is either NULL or unknown", ComponentRoot::LEVEL_WARNING);
        }
        return true;
    }
}