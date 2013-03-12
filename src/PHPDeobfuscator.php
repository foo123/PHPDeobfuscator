<?php
/**
*    PHP Deobfuscator class
*    uses PHP_Beautifier PEAR Class
*    
*    http://nikos-web-development.netai.net/
*
**/
class __PHP_Deobfuscator__
{
    private $__beautiful__=false;
    private $__output__=false;
    private $__ffile__=false;
    private $__ffolder__=false;
    
    public function __construct($fil, $out, $b=false, $o=false)
    {
        $this->__beautiful__=$b;
        $this->__output__=$o;
        //$args=$this->___get_args___();
        $this->__ffile__=$fil; //isset($args['f'])?$args['f']:false;
        //$this->__ffolder__=isset($args['d'])?$args['d']:false;
        $this->__beautiful__=true; //isset($args['b'])?true:false;
        $this->__output__=$out; //isset($args['o'])?$args['o']:false;
    }
    
    private function ___get_args___()
    {
        $args = array();
        for ($i=1; $i<count($_SERVER['argv']); $i++)
        {
            $arg = $_SERVER['argv'][$i];
            if ($arg{0} == '-' && $arg{1} != '-')
            {
                for ($j=1; $j < strlen($arg); $j++)
                {
                    $key = $arg{$j};
                    $value = $_SERVER['argv'][$i+1]{0} != '-' ? preg_replace(array('/^["\']/', '/["\']$/'), '', $_SERVER['argv'][++$i]) : true;
                    $args[$key] = $value;
                }
            }
            else
                $args[] = $arg;
        }
        return $args;
    }

    private function ____deobfuscate_str____($__s__)
    {
        //$__has_start_tag__=false;
        //$__has_end_tag__=false;
        $__function_variable_names__ = array("__s__"=> 0, "__matches__"=> 0, "__function_variable_names__"=> 0, "__created__" => 0, "__before_eval_vars__" => 0);
        
        // remove php tags
        // remove new lines from edges
        $__s__=trim($__s__);
        /*if (strpos($__s__, '<?php')===0)
        {
            $__s__=substr($__s__, 5);
            $__has_start_tag__=true;
        }
        if (strpos($__s__, '?>')===strlen($__s__)-2)
        {
            $__s__=substr($__s__, 0, strlen($__s__)-2);
            $__has_end_tag__=true;
        }*/
        
        // deobfuscation loop, obfuscated code (almost always) has multiple layers of eval'd code whithin eval'd code
        while(1==preg_match('/(^|\b)eval\b/', $__s__, $__matches__, PREG_OFFSET_CAPTURE)) 
        {
            // replace eval with print, to output deobfuscated code
            $__s__ = substr_replace($__s__, 'print', $__matches__[0][1], 4);

            // remove php tags
            /*if (0===strpos($__s__, '<?php'))
            {
                $__s__="?>$__s__";
            }*/
            
            // get vars before eval
            $__before_eval_vars__ = get_defined_vars();
            //print_r(array_keys($__before_eval_vars__));
            // eval / catch output
            ob_start();
            eval($__s__);
            $__s__ = ob_get_clean();
            
            // Let's extract the variables that were defined AFTER the call to 'eval'
            // We can generate a list of the newly created variables by substracting the list of the variables of the function and the list of the variables which existed before the call to the list of current variables at this point
            $__created__ = array_diff_key(get_defined_vars(), $GLOBALS, $__function_variable_names__, $__before_eval_vars__);
            // Now we globalize them and extract them, so script eval can continue
            /*foreach ($__created__ as $____created_name____=>$______vv_______)  
                global $$____created_name____;*/
            extract($__created__);
            //print_r(array_keys($__created__));
        }
        
        // add any php tag if needed
        /*if ($__has_start_tag__)
        {
            $__s__="<?php\n".$__s__;
        }
        if ($__has_end_tag__)
        {
            $__s__.="\n?>";
        }*/
        return $__s__;
    }

    private function ____getEvalStr____($str)
    {
        if (1==preg_match('/(^|\b)(eval)\b/', $str, $matches, PREG_OFFSET_CAPTURE))
        {
            $substr=substr($str, $matches[2][1]+4);
            $parens=array();
            $inside='';
            $i=0;
            while($i<strlen($substr))
            {
                $char=$substr{$i};
                $inside.=$char;
                if ($char=='(')
                {
                    array_push($parens, $i);
                }
                elseif ($char==')')
                {
                    array_pop($parens);
                    if (empty($parens))
                    {
                        break;
                    }
                }
                $i++;
            }
            $inside='eval'.$inside.';';
            return array('str'=>$inside, 'pos'=>$matches[2][1], 'end'=>$matches[2][1]+strlen($inside));
        }
        return false;
    }
    
    private function ____deobfuscate_file____($__f__)
    {
        // copy file
        copy($__f__, $__f__.'.orig');
        $__s__=file_get_contents($__f__);
        
        // insert the deobfuscation code into the file to be executed in the appropriate scope
         $__deobf_loop__='
        // eval scanner
        function _________getEvalStr_________($str)
        {
            if (1==preg_match("/(^|\b)(eval)\b/", $str, $matches, PREG_OFFSET_CAPTURE))
            {
                $parens=array();
                $inside="";
                $i=$matches[2][1]+4;
                while($i<=strlen($str))
                {
                    $char=$str{$i};
                    $inside.=$char;
                    if ($char=="(")
                    {
                        array_push($parens, $i);
                    }
                    elseif ($char==")")
                    {
                        array_pop($parens);
                        if (empty($parens))
                        {
                            break;
                        }
                    }
                    $i++;
                }
                return array("str"=>"eval".$inside.";","pos"=>$matches[2][1]);
            }
            return false;
        }
        // deobfuscation loop, obfuscated code (almost always) has multiple layers of evald code whithin evald code
        $______s______="%%%%%%%%%%%%%EVALSTR%%%%%%%%%%%%%%%%%%%%%%";
        $_______match_________=_________getEvalStr_________($______s______);
        while(false!==$_______match_________) 
        {
            // replace eval with print, to output deobfuscated code
            $______s______ = substr_replace($______s______, "print", $_______match_________["pos"], 4);
            
            // eval / catch output
            ob_start();
            eval($______s______);
            $______s______ = ob_get_clean();
            $_______match_________=_________getEvalStr_________($______s______);
        }
        print($______s______);
';
       $__match__=$this->____getEvalStr____($__s__);
        if (false!==$__match__)
        {
            $__s__=substr_replace($__s__, str_replace('%%%%%%%%%%%%%EVALSTR%%%%%%%%%%%%%%%%%%%%%%', $__match__['str'], $__deobf_loop__), $__match__['pos'], strlen($__match__['str']));
            file_put_contents($__f__, $__s__);
            ob_start();
            include($__f__);
            $__s__=ob_get_clean();
        }
        return $__s__;
    }
    
    private function ___beautify___($ugly_source, $params=array())
    {
        require_once 'PHP/Beautifier.php';
        $oBeautifier = new PHP_Beautifier(); 
        $default_params=array_merge(array(
            'indent_char'=> ' ',
            'indent_number'=> 4,
            'new_line'=> PHP_EOL,
            'style'=> 'allman'
        ),$params);
        
        // add some filters if not exist already
        $oBeautifier->addFilterDirectory(dirname(__FILE__).DIRECTORY_SEPARATOR.'Filter');
        // Add filters for array and code indentation style
        $oBeautifier->addFilter('ArrayNested');
        $oBeautifier->addFilter('IndentStyles',array('style'=>$default_params['style']));
        // Set the indent char, number of chars to indent and newline char
        $oBeautifier->setIndentChar($default_params['indent_char']);
        $oBeautifier->setIndentNumber($default_params['indent_number']);
        $oBeautifier->setNewLine($default_params['new_line']);
        
        // Define the input file
        $oBeautifier->setInputString($ugly_source); 
        // Process the file. DON'T FORGET TO USE IT
        $oBeautifier->process();
        // return beautiful source
        return $oBeautifier->get();
    }

    private function ___processFile___($file)
    {
        if ($file && is_file($file))
        {
            $source=$this->____deobfuscate_file____($file);
            if ($this->__beautiful__)
                $source=$this->___beautify___($source);
            return $source;
        }
        return '';
    }
    
    public function process()
    {
        if ($this->__output__)
            file_put_contents($this->__output__, $this->___processFile___($this->__ffile__));
        else
            echo $this->___processFile___($this->__ffile__);
    }
}
?>