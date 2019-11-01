<?php

namespace vdf;

use vdf\VDFException;
use php\util\Regex;

/**
 * Class VDF
 *
 * @author GIGNIGHT
 * @link gignight.ru / vk.com/gignight1337
 */
class VDF
{

    /**
     * @param string $data
     * @throws VDFException
     * @return array
     */
    public static function decode($data)
    {
        if(!is_string($data))
            throw new VDFException("decode expects parameter 1 to be a string, " . gettype($data) . " given.");


        $result      = [];
        $bOpen       = '{';
        $bClose      = '}';
        $mark        = '"';
        $backSlash   = '\\';
        $data        = trim(Regex::of('#^\s*//.+$#m')->with($data)->replace(null));
        $inKey       = false;
        $inValue     = false;
        $inSubArray  = false;
        $openBracketCount = 0;
        $buffer      = null;
        $key         = null;
        $value       = null;
        $lastChar    = null;
        
        for ($i = 0; $i < strlen($data); $i++)
        {
            $char = $data[$i];
            
            if ($inSubArray)
            {
                if ($lastChar == $backSlash)
                {
                    $buffer .= $char;
                }
                else
                {
                    if ($char == $bClose && $openBracketCount == 0)
                    {
                        $value = self::decode(trim($buffer));
                        $buffer = null;
                        $inSubArray = false;
                        
                        if ($key != null)
                            $result[$key] = $value;
                        else
                            $result = $value;
                        
                        $key = null;
                        $value = null;
                    }
                    elseif ($char == $bClose)
                    {
                        $openBracketCount--;
                        $buffer .= $char;
                    }
                    elseif ($char == $bOpen)
                    {
                        $openBracketCount++;
                        $buffer .= $char;
                    }
                    else
                    {
                        $buffer .= $char;
                    }
                }
            }
            elseif ($inKey)
            {
                if ($char == $mark && $lastChar !== $backSlash)
                {
                    $key = $buffer;
                    $buffer = null;
                    $inKey = false;
                }
                elseif ($char !== $backSlash)
                {
                    $buffer .= $char;
                }
            }
            elseif ($inValue)
            {
                if ($char == $mark && $lastChar !== $backSlash)
                {
                    $value = $buffer;
                    $buffer = null;
                    $inValue = false;
                    $result[$key] = $value;
                    $key = null;
                    $value = null;
                }
                elseif ($char !== $backSlash)
                {
                    $buffer .= $char;
                }
            }
            else
            {
                if ($char == $mark && $key == null)
                {
                    $inKey = true;
                }
                elseif ($char == $mark && $value == null)
                {
                    $inValue = true;
                }
                elseif ($char == $bOpen)
                {
                    $inSubArray = true;
                    $openBracketCount = 0;
                }
            }
            $lastChar = $char;
        }
        
        return $result;
    }

    /**
     * @param array $data
     * @param bool $prettyPrint
     * @param int $level
     * @throws VDFException
     * @return string
     */
    public static function encode($data, $prettyPrint = true, $level = 0)
    {
        if(!is_array($data))
            throw new VDFException("encode encounted " . gettype($data) . ", only array or string allowed (depth ".$level.")");
            

        $buffer = "";
        $line = ($prettyPrint) ? str_repeat("\x09", $level) : "";
        
        foreach($data as $key => $val)
        {
            if(is_string($val) || is_int($val))
            {
                $buffer .= "$line\"$key\"\t\"$val\"\n";
            }
            else
            {
                $res = self::encode($val, $prettyPrint, $level + 1);
                if($res === null) return null;
                $buffer .= "$line\"$key\"\n$line{\n$res$line}\n";
            }
        }
        
        return $buffer;
    }
    
    /**
     * @param string $filename
     * @throws VDFException
     * @return array
     */
    public static function fromFile($filename)
    {
        return self::decode(file_get_contents("{$filename}"));
    }
    
    /**
     * @param string $filename
     * @param array $data
     * @throws VDFException
     */
    public static function toFile($filename, $data)
    {
        file_put_contents("{$filename}", self::encode($data));
    }
}