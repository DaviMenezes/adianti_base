<?php
namespace Adianti\Base\Lib\Widget\Template;

use Adianti\Base\App\Lib\Util\ApplicationTranslator;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Html Renderer
 *
 * @version    5.0
 * @package    widget
 * @subpackage template
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class THtmlRenderer
{
    private $path;
    private $buffer;
    private $template;
    private $sections;
    private $replacements;
    private $enabledSections;
    private $repeatSection;
    private $enabledTranslation;
    
    /**
     * Constructor method
     *
     * @param $path  HTML resource path
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new Exception(AdiantiCoreTranslator::translate('File not found').': ' . $path);
        }
        $this->enabledSections = array();
        $this->enabledTranslation = false;
        $this->buffer = array();
        
        if (file_exists($path)) {
            $this->template = file_get_contents($path);
        }
    }
    
    /**
     * Enable translation inside template
     */
    public function enableTranslation()
    {
        $this->enabledTranslation = true;
    }
    
    /**
     * Enable a HTML section to show
     *
     * @param $sectionName Section name
     * @param $replacements Array of replacements for this section
     * @param $repeat Define if the section is repeatable
     */
    public function enableSection($sectionName, $replacements = null, $repeat = false)
    {
        $this->enabledSections[] = $sectionName;
        $this->replacements[$sectionName] = $replacements;
        $this->repeatSection[$sectionName] = $repeat;
    }
    
    /**
     * Replace the content with array of replacements
     *
     * @param $replacements array of replacements
     * @param $content content to be replaced
     */
    private function replace(&$replacements, $content)
    {
        if (is_array($replacements)) {
            foreach ($replacements as $variable => $value) {
                if (is_scalar($value)) {
                    $content = str_replace('{$'.$variable.'}', $value, $content);
                    $content = str_replace('{{'.$variable.'}}', $value, $content);
                } elseif (is_object($value)) {
                    if (method_exists($value, 'show')) {
                        ob_start();
                        $value->show();
                        $output = ob_get_contents();
                        ob_end_clean();
                        $content = str_replace('{$'.$variable.'}', $output, $content);
                        $content = str_replace('{{'.$variable.'}}', $output, $content);
                        $replacements[$variable] = $output;
                    }
                    
                    if (method_exists($value, 'getAttributes')) {
                        $vars = $value->getAttributes();
                        $vars[] = $value->getPrimaryKey();
                    } elseif (!$value instanceof self) {
                        $vars = array_keys(get_object_vars($value));
                    }
                    
                    if (isset($vars)) {
                        foreach ($vars as $propname) {
                            $content = str_replace('{$'.$variable.'->'.$propname.'}', $value->$propname, $content);
                            $content = str_replace('{{'.$variable.'->'.$propname.'}}', $value->$propname, $content);
                        }
                    }
                } elseif (is_null($value)) {
                    $content = str_replace('{$'.$variable.'}', '', $content);
                    $content = str_replace('{{'.$variable.'}}', '', $content);
                } elseif (is_array($value)) { // embedded repeated section
                    // there is a template for this variable
                    if (isset($this->buffer[$variable])) {
                        $tpl = $this->buffer[$variable];
                        $agg = '';
                        foreach ($value as $replace) {
                            $agg .= $this->replace($replace, $tpl);
                        }
                        $content = str_replace('{{'.$variable.'}}', $agg, $content);
                    }
                }
            }
        }
        return $content;
    }
    
    /**
     * Show the HTML and the enabled sections
     */
    public function show()
    {
        $opened_sections = array();
        $sections_stack = array('main');
        $array_content = array();
        
        if ($this->template) {
            $content = $this->template;
            if ($this->enabledTranslation) {
                $content  = ApplicationTranslator::translateTemplate($content);
            }
            
            $array_content = preg_split('/\n|\r\n?/', $content);
            $sectionName = null;
            
            // iterate line by line
            foreach ($array_content as $line) {
                $line_clear = trim($line);
                $line_clear = str_replace("\n", '', $line_clear);
                $line_clear = str_replace("\r", '', $line_clear);
                $delimiter  = false;
                
                // detect section start
                if ((substr($line_clear, 0, 5)=='<!--[') and (substr($line_clear, -4) == ']-->') and (substr($line_clear, 0, 6)!=='<!--[/')) {
                    $previousSection = $sectionName;
                    $sectionName = substr($line_clear, 5, strpos($line_clear, ']-->')-5);
                    $sections_stack[] = $sectionName;
                    $this->buffer[$sectionName] = '';
                    $opened_sections[$sectionName] = true;
                    $delimiter  = true;
                    
                    $found = self::recursiveKeyArraySearch($previousSection, $this->replacements);
                    
                    // turns section repeatable if it occurs inside parent section
                    if (isset($this->replacements[$previousSection][$sectionName]) or
                        isset($this->replacements[$previousSection][0][$sectionName]) or
                        isset($found[$sectionName]) or
                        isset($found[0][$sectionName])) {
                        $this->repeatSection[$sectionName] = true;
                    }
                    
                    // section inherits replacements from parent session
                    if (isset($this->replacements[$previousSection][$sectionName])) {
                        $this->replacements[$sectionName] = $this->replacements[$previousSection][$sectionName];
                    }
                }
                // detect section end
                elseif ((substr($line_clear, 0, 6)=='<!--[/')) {
                    $delimiter  = true;
                    $sectionName = substr($line_clear, 6, strpos($line_clear, ']-->')-6);
                    $opened_sections[$sectionName] = false;
                    
                    array_pop($sections_stack);
                    $previousSection = end($sections_stack);
                    
                    // embbed current section as a variable inside the parent section
                    if (isset($this->repeatSection[$previousSection]) and $this->repeatSection[$previousSection]) {
                        $this->buffer[$previousSection] .= '{{'.$sectionName.'}}';
                    } else {
                        // if the section is repeatable and the parent is not (else), process replaces recursively
                        if ((isset($this->repeatSection[$sectionName]) and $this->repeatSection[$sectionName])) {
                            $processed = '';
                            // if the section is repeatable, repeat the content according to its replacements
                            if (isset($this->replacements[$sectionName])) {
                                foreach ($this->replacements[$sectionName] as $iteration_replacement) {
                                    $processed .= $this->replace(
 
                                        $iteration_replacement,
                                                                 $this->buffer[$sectionName]
 
                                    );
                                }
                                print $processed;
                                $processed = '';
                            }
                        }
                    }
                    
                    $sectionName = end($sections_stack);
                } elseif (in_array($sectionName, $this->enabledSections)) { // if the section is enabled
                    if (!$this->repeatSection[$sectionName]) { // not repeatable, just echo
                        // print the line with the replacements
                        if (isset($this->replacements[$sectionName])) {
                            print $this->replace($this->replacements[$sectionName], $line . "\n");
                        } else {
                            print $line . "\n";
                        }
                    }
                }
                
                if (!$delimiter) {
                    if (!isset($sectionName)) {
                        $sectionName = 'main';
                        if (empty($this->buffer[$sectionName])) {
                            $this->buffer[$sectionName] = '';
                        }
                    }
                    
                    $this->buffer[$sectionName] .= $line . "\n";
                }
            }
        }
        
        // check for unclosed sections
        if ($opened_sections) {
            foreach ($opened_sections as $section => $opened) {
                if ($opened) {
                    throw new Exception(AdiantiCoreTranslator::translate('The section (^1) was not closed properly', $section));
                }
            }
        }
    }
    
    /**
     * Static search in memory structure
     */
    public static function recursiveKeyArraySearch($needle, $haystack)
    {
        foreach ($haystack as $key=>$value) {
            if ($needle === $key) {
                return $value;
            } elseif (is_array($value) && self::recursiveKeyArraySearch($needle, $value) !== false) {
                return self::recursiveKeyArraySearch($needle, $value);
            }
        }
        return false;
    }
    
    /**
     * Returns the HTML content as a string
     */
    public function getContents()
    {
        ob_start();
        $this->show();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
