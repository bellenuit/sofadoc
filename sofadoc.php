<?php
	
/**
 *	SofaDoc simple code documentation
 *	
 *  @author Matthias Bürcher 2010 matti@belle-nuit.com
 *  @link https://www.sofawiki.com
 *	@version 1.0.0
 *  
 */
	
	

/**
 *	Tokenizes one php file and returns some features in an array
 *
 *  @param $file path to file
 */

function swReadCode($file)
{
	$source = file_get_contents($file);
	$tnames = token_get_all($source);
	
	foreach($tnames as $elem)
	{
		if ($state == 'T_OBJECT_OPERATOR') 
		{
			$state = '';
			continue; // ignore call to methods and properties
		}
		
		if (is_array($elem))
		{
			$tn = token_name($elem[0]);
			$elem[0] = $tn;
			$lines[] = '<p>'.print_r($elem, true);
			
			switch($tn)
			{
				case 'T_DOC_COMMENT': $comments[] = $elem[1]; $currentcomment = $elem[1]; break;
				case 'T_COMMENT':   if (stristr($elem[1],'SOFADOC_IGNORE'))
									{
										$s = str_replace('/*','',$elem[1]);
										$s = str_replace('*/','',$s);
										$s = str_replace('SOFADOC_IGNORE','',$s);
										$s = trim($s);
										$ignorelist[] = $s;
									}
									elseif (stristr($elem[1],'SOFADOC_INCLUDE'))
									{
										$s = str_replace('/*','',$elem[1]);
										$s = str_replace('*/','',$s);
										$s = str_replace('SOFADOC_INCLUDE','',$s);
										$s = trim($s);
										$includes[]= $s; break;
									}
									break;
				case 'T_VARIABLE': switch($state)
								 {
									 case 'T_FUNCTION': $functionelem[]=$elem[1]; break;		 
									 case 'T_GLOBAL' : $usedglobals[] = $elem[1]; $state = ''; break;
									 case 'T_INCLUDE' : 
									 case 'T_INCLUDE_ONCE': $includeselem .= $elem[1]; break;
									 default: 
									 if ($classindent==1 && $currentclass)
									 {
									 	 
									 	  $properties[$currentclass][] = $elem[1]; 

									 }
									 elseif ($classindent)
									 {
									 	  
									 	  $variables[] = $elem[1]; 
									 }
									 else
									 {
										  $usedglobals[] = $elem[1];
									 }
									 $state = $tn;
									 break;
									 
								 }
								 break;
				case 'T_CLASS':		   $classelem = array(); $state = $tn; break;
				case 'T_FUNCTION':     $functionelem = array(); $state = $tn; break;
				case 'T_GLOBAL' :      $state = $tn; break;
				case 'T_INCLUDE':      $state = $tn; break;
				case 'T_INCLUDE_ONCE': $state = $tn; break;
				case 'T_NEW'	:	   $state = $tn; break;
				case 'T_STRING':
				case 'T_CONSTANT_ENCAPSED_STRING': switch($state)
								 {
									 case 'T_CLASS' : if (!count($classelem))
									 				  {
										 				  $classcomment[$elem[1]] = $currentcomment; 
										 				  $currentcomment = '';
										 				  $currentclass = $elem[1];
										 				  $classindent = 0;
										 			  }
										 			  $classelem[]= $elem[1]; break;
									 case 'T_INCLUDE' : 
									 case 'T_INCLUDE_ONCE': $includeselem .= $elem[1]; break;
									 case 'T_NEW': $usedclasses[] = $elem[1]; $state = ''; break;
									 case 'T_FUNCTION': if (!count($functionelem))
									 				  {
										 				  if ($currentclass)
										 				  {
											 				  $methodcomment[$currentclass][$elem[1]] = $currentcomment; 
										 				  }
										 				  else
										 				  {
										 				 	  $functioncomment[$elem[1]] = $currentcomment; 
										 				  }
										 				  $currentcomment = '';
										 			  }
										 			  $functionelem[]= $elem[1]; break;
									 default: switch ($elem[1])
									 		{
											 	case 'PHP_EOL':
											 	case 'defined':
											 	case 'define':
											 	case 'null':
											 	case 'NULL':
											 	case 'true':
											 	case 'TRUE':
											 	case 'FALSE':
											 	case 'false': break;
											 	case 'in_array':
											 	case 'sort':
											 	case 'ksort':
											 	case 'rsort':
											 	case 'usort':
											 	case 'join':
											 	case 'count':
											 	case 'explode':
											 	case 'implode':
											 	case 'natsort':
											 	case 'arsort':
											 	case 'uasort':
											 	case 'asort':
											 	case 'krsort':
											 	case 'shuffle':
											 	case 'is_array': $features[] = 'array'; break;
											 	case 'debug_print_backtrace':  $features[] = 'error'; break;
											 	case 'utf8_encode':
											 	case 'utf8_decode':
											 	case 'libxml_use_internal_errors': $features[] = 'xml'; break;
											 	case 'memory_get_usage':
											 	case 'phpversion':
											 	  $features[] = 'php'; break;
											 	case 'length':
											 	case 'trim':
											 	case 'ltrim':
											 	case 'rtrim':
											 	case 'md5':
											 	case 'md5_file':
											 	case 'sprintf':
											 	case 'chr':
											 	case 'ord':
											 	case 'bin2hex':
											 	case 'substr':
											 	case 'number_format': 
											 	case 'substr_count': 
											 	case 'html_entity_decode':
											 	case 'chunk_split':
											 	case 'html_entity_encode':
											 	case 'htmlspecialchars':  $features[] = 'string'; break;
											 	case 'abs':
											 	case 'cos':
											 	case 'exp':
											 	case 'log':
											 	case 'log10':
											 	case 'ceil': 
											 	case 'floor': 
											 	case 'round': 
											 	case 'pow':
											 	case 'sin': 
											 	case 'tan': 
											 	case 'sqrt': 
											 	case 'max': 
											 	case 'min': 
											 	case 'rand': 
											 	case 'decbin': 
											 	case 'hexdec':$features[] = 'math'; break;
											 	case 'dirname':
											 	case 'fopen':
											 	case 'fclose':
											 	case 'fwrite':
											 	case 'fread':
											 	case 'chmod':
											 	case 'fseek':
											 	case 'ftell':
											 	case 'flock':
											 	case 'feof':
											 	case 'LOCK_EX':
											 	case 'LOCK_UN':
											 	case 'disk_free_space':
											 	case 'filesize':
											 	case 'file_exists':
											 	case 'filemtime': 
											 	case 'is_dir':
											 	case 'mkdir':
											 	case 'opendir':
											 	case 'readdir':
											 	case 'unlink':
											 	case 'copy':
											 	case 'glob':
											 	case 'file_get_contents': 
											 	case 'file_put_contents': 
											 	case 'fileperms':
											 	case 'is_uploaded_file':
											 	case 'move_uploaded_file': $features[] = 'file'; break;
											 	case 'print_r': 
											 	case 'serialize':
											 	case 'unserialize':
											 	case 'is_numeric': 
											 	case 'is_object':
											 	case 'is_resource':
											 	case 'is_string':
											 	case 'floatval': 
											 	case 'intval':  $features[] = 'variable'; break;
											 	case 'get_object_vars':
											 	case 'is_a':  $features[] = 'class'; break;
											 	case 'time':
											 	case 'date':
											 	case 'microtime': $features[] = 'time'; break;
											 	case 'imagejpeg': $features[] = 'image'; break;
											 	case 'function_exists':  $features[] = 'function'; break;
											 	case 'setcookie': $features[] = 'network'; break;
											 	case 'mail': $features[] = 'mail'; break;
											 	case 'date_default_timezone_set': $features[] = 'date'; break;
											 	case 'sleep':
											 	case 'pack': $features[] = 'misc'; break;
											 	default: 
											 	$t = substr($elem[1],0,1);
											 	$t2 = substr($elem[1],0,2);
											 	$t3 = substr($elem[1],0,3);
											 	$t4 = substr($elem[1],0,4);
											 	$t5 = substr($elem[1],0,5);
											 	$t6 = substr($elem[1],0,6);
											 	$t7 = substr($elem[1],0,7);
											 	if ($t != "'" && $t != '"' ) 
											 	{	

											 		if ($t2== 'E_')
											 			$features[] = 'error';
											 		if ($t2== 'mb')
											 			$features[] = 'mb';
											 		elseif ($t3 == 'str')
											 			$features[] = 'str';
											 		elseif ($t3 == 'dba')
											 			$features[] = 'dba';
											 		elseif ($t3 == 'url')
											 			$features[] = 'url';
											 		elseif ($t3 == 'ini')
											 			$features[] = 'ini';
											 		elseif ($t3 == 'zip' || $t3 == 'ZIP')
											 			$features[] = 'zip';
											 		elseif ($t4 == 'curl' || $t4 == 'CURL')
											 			$features[] = 'curl';
											 		elseif ($t4 == 'preg' || $t4 == 'PREG')
											 			$features[] = 'preg';
											 		elseif ($t4 == 'json')
											 			$features[] = 'json';
											 		elseif ($t4 == 'IMG_')
											 			$features[] = 'image';
											 		elseif ($t4 == 'SORT')
											 			$features[] = 'array';
											 		elseif ($t5 == 'error')
											 			$features[] = 'error';
											 		elseif ($t5 == 'iconv')
											 			$features[] = 'iconv';
											 		elseif ($t5 == 'array')
											 			$features[] = 'array';
											 		elseif ($t5 == 'Image' || $t5 == 'image')
											 			$features[] = 'image';
											 		elseif ($t7 == 'session')
											 			$features[] = 'session';
											 		elseif ($t7 == 'sqlite3' ||$t7 == 'SQLITE3')
											 			$features[] = 'sqlite3';
											 		else
											 			$others[] = $elem[1];
											 	} 
									 		}
								 }
								 break;
				case 'T_EXTENDS': $classelem[]= 'extends'; break;
				case 'T_TRY':
				case 'T_THROW':
				case 'T_CATCH' : $features[] = 'exception'; break;
				case 'T_OBJECT_OPERATOR': $state = $tn; break;
				case 'T_EMPTY';
				case 'T_AND_EQUAL':
				case 'T_ARRAY':
				case 'T_AS':
				case 'T_BOOLEAN_AND':
				case 'T_BOOLEAN_OR':
				case 'T_BREAK':
				case 'T_CASE':
				case 'T_CLOSE_TAG':
				case 'T_CONCAT_EQUAL':
				case 'T_CONTINUE':
				case 'T_DEC':
				case 'T_DEFAULT':
				case 'T_DIV_EQUAL':
				case 'T_DNUMBER':
				case 'T_DO':
				case 'T_DOUBLE_ARROW':
				case 'T_DOUBLE_COLON':
				case 'T_ECHO':
				case 'T_ELSE':
				case 'T_ELSEIF':
				case 'T_ENCAPSED_AND_WHITESPACE':
				case 'T_EXIT':
				case 'T_FILE':
				case 'T_FOR':
				case 'T_FOREACH':
				case 'T_IF':
				case 'T_INC':
				case 'T_INT_CAST':
				case 'T_ISSET':
				case 'T_IS_EQUAL':
				case 'T_IS_IDENTICAL';
				case 'T_IS_GREATER_OR_EQUAL':
				case 'T_IS_NOT_EQUAL':
				case 'T_IS_NOT_IDENTICAL':
				case 'T_IS_SMALLER_OR_EQUAL':
				case 'T_LOGICAL_AND';
				case 'T_LOGICAL_OR':
				case 'T_LNUMBER':
				case 'T_MINUS_EQUAL':
				case 'T_MOD_EQUAL':
				case 'T_MUL_EQUAL':
				case 'T_OPEN_TAG':
				case 'T_PLUS_EQUAL':
				case 'T_PRIVATE':
				case 'T_RETURN';
				case 'T_SL':
				case 'T_SR':
				case 'T_STATIC':
				case 'T_SWITCH':
				case 'T_UNSET':
				case 'T_VAR':
				case 'T_WHILE':
				case 'T_WHITESPACE': break;
				
				default: $tokens[] = $tn; $rest[] = print_r($elem,true);
			}
			
		}
		else
		{
			$lines[] = $elem;
			switch($elem)
			{
				case ';': switch($state)
						  {
									 case 'T_INCLUDE' : 
									 case 'T_INCLUDE_ONCE': $includes[] = $includeselem; $includeselem = ''; $state = ''; break;
						   } break;
				case '{': $classindent++; 
						  switch($state)
						  {
									 case 'T_CLASS' :  $classes[] = $classelem; unset($classelem); $state = ''; 
									 				   break;
						   }break;
				case '}': $cassindent--;
						  if (!$classindent) $currentclass = ''; break;
				case ')': switch($state)
						  {
									 case 'T_FUNCTION' :  $functionelem[]= ')'; 
									 					  if ($currentclass)
									 					  {
										 					  $methods[$currentclass][] = $functionelem;
										 				  }
										 				  else
										 				  {
										 				  	$functions[] = $functionelem; 
										 				  }
									 					  unset($functionelem); 
									 					  $state = '';
									 					  break;
						   }break;
			    case '(': switch($state)
						  {
									 case 'T_FUNCTION' :  if(!count($functionelem)) 
									 					  {
										 					 $state = '';  // anonymous function
										 				  }
										 				  else
										 				  {
									 					 	 $functionelem[]= '(';
									 					  }
									 					  break;
						   } break;
			    case ',': switch($state)
						  {
									 case 'T_FUNCTION' :  $functionelem[]= ',';  break;
						   }break;
			    case '=': switch($state)
						  {
									 case 'T_FUNCTION' :  $functionelem[]= '=';  break;
						   }break;
	
			}
		}
			
	}
	
	sort($classes);
	sort($functions);
	$usedclasses = array_unique($usedclasses);
	sort($usedclasses);
	$usedfunctions = array_unique($usedfunctions);
	sort($usedfunctions);
	$usedglobals = array_unique($usedglobals);
	sort($usedglobals);
	$variables = array_unique($variables);
	sort($variables);
	$includes = array_unique($includes);
	sort($includes);
	$features = array_unique($features);
	sort($features);
	$others = array_unique($others);
	// $others = array_diff($others, $definedfunctions);
	sort($others);
	$tokens = array_unique($tokens);
	sort($tokens);
	//sort($methods);
	//sort($properties);
	
	$result['comments'] = $comments;
	$result['classes'] = $classes;
	$result['methods'] = $methods;
	$result['properties'] = $properties;
	$result['functions'] = $functions;
	$result['usedclasses'] = $usedclasses;
	$result['usedfunctions'] = $usedfunctions;
	$result['usedglobals'] = $usedglobals;
	$result['variables'] = $variables;
	$result['includes'] = $includes;
	$result['features'] = $features;
	$result['others'] = $others;
	$result['tokens'] = $tokens;
	$result['singlecomments'] = $singlecomments;
	$result['ignorelist'] = $ignorelist;
	$result['source'] = $source;
	$result['tnames'] = $tnames;
	return $result;
	
}

/**
 *	Returns a doc comment as HTML
 *
 *  @param $comments comment as returned by PHP Tokenizer
 *  @param $firstline use only pitch in first line
 */


function swPrintComment($comment, $firstline = false)
{
	$lines = explode(PHP_EOL,$comment);
	$printlines = [];
	foreach ($lines as $line)
	{
		if (stristr($line,'/**')) continue;
		if (stristr($line,'*/')) continue;
		$line = trim($line);
		if (substr($line,0,1)== '*') $line = trim(substr($line,1));
		$printlines[] = $line;
	}	
	if ($firstline) return '<i>'.$printlines[0].'</i>';
	return '<i>'.join('<br>',$printlines).'</i>';
}

/**
 *	Transform a parent-children list into a tree array
 *
 *  @param $list array of parent-children relation ships (key is parentid = 0 if root, value fields are id, parentid and label
 *  @param $parent 
 */

function swCreateTree(&$list, $parent)
{
    $tree = array();
    foreach ($parent as $k=>$l)
    {
        if(isset($list[$l['id']]))
        {
            $l['children'] = swCreateTree($list, $list[$l['id']]);
        }
        $tree[$l['id']] = $l;
    } 
    ksort($tree);
    return $tree;
}

/**
 *	Returns a tree array as HTML 
 *
 *  @param $list tree array
 */

function swPrintTree($list)
{
    $result = '<ul>';
    foreach($list as $elem)
    {
	    if (isset($elem['id'])) $result .= '<li>'.$elem['label'].'</li>';
	    if (isset($elem['children'])) $result .= swPrintTree($elem['children']);

    }
    $result .= '</ul>';
    return $result;   
}


// main


// header
echo '<html><heade><title>SofaDoc '.$file.'</title></head></body>';
echo '<p>SofaDoc <a href="sofadoc.php?">Map</a> <a href="sofadoc.php?usage=1">Usage</a>';

// usage
if (isset($_GET['usage']))
{
	echo '<h1>Usage</h1>';
	echo '<p>SofaDoc is a simple live documentation of PHP source code. SofaDoc recognizes JavaDoc style documentation, but does not need it. It parses the code with the PHP Tokenizer and extracts some features. 
<ul>
<li>Classes defined and used</li>
<li>Functions defined and used</li>
<li>Globals and variables</li>
<li>Includes</li>
<li>Features: what group of PHP built in functions are used</li>
</ul>
<p>Usage: Just drop the sofadoc.php file in the main folder of your code and it will explore the files starting with index.php. There is no configuration needed. The documentation is very fast (less than a second for the entire sofawiki codebase).
<p>Tuning:
<ul>
<li>If your main file is not index.php, you need to create an index.php file and include the main file from it.</li>
<li>If you want to exclude a file from the Doc (e.g. a configuration file), define the path with a variable and then include the variable so it is hidden from SofaDoc.</li>
<li>Paths are resolved relative to sofadoc.php folder. If you use a variable for the absolute path, you can ignore it with a comment <b>/'.'* SOFADOC_IGNORE $swRoot/ *'.'/</b>.</li>
<li>You can add include paths with a comment <b>/'.'* SOFADOC_INCLUDE inc/skins/default.php *'.'/</b>.</li>
</ul>
<p>Limitations
<ul>
<li>The parser is not perfect. It has only be tested against Sofawiki code. If elements are not recognized, sections other and token appear.</li>
<li>Only include paths with constants are followed. This exclude features like autoload. Namespaces are not supported either.</li>
</ul>';
	echo '</body></html>';
	return;

}

// Parse all files starting with joblist. We need all files to make the used classes and functions linkable. It is fast enough for the sofawiki project.
$joblist  = array('index.php');

// Create the filetree for the map.
$filetree[0][] = array('id'=>'index.php', 'parentid'=>0, 'label'=>'<a href="sofadoc.php?file=index.php">index.php</a>');
$classtree = array();

while (count($joblist))
{
	$job = array_pop($joblist);
	$results[$job] = swReadCode($job);
	
	// post process results to create global indexes
	
	$includescomments[$job] = swPrintComment($results[$job]['comments'][0],true);
	
	foreach($results[$job]['classes'] as $c)
	{
		$classlinks[$c[0]] = '<a href="sofadoc.php?file='.$job.'">'.$c[0].'</a>';
		
		if ($c[1] == 'extends')
		{
			$classtree[$c[2]][] = array('id'=>$c[0], 'parentid'=>$c[2],'label'=>'<a href="sofadoc.php?file='.$job.'#'.$c[0].'">'.$c[0].'</a>');

		}
		else
		{
			$classtree[0][] = array('id'=>$c[0], 'parentid'=>0,'label'=>'<a href="sofadoc.php?file='.$job.'#'.$c[0].'">'.$c[0].'</a>');
		}	
	}
	foreach($results[$job]['usedglobals'] as $g) $allglobals[] = $g;
		
	foreach($results[$job]['functions'] as $f) $functionlinks[$f[0]] = '<a href="sofadoc.php?file='.$job.'#'.$f[0].'">'.$f[0].'</a>';
		
	foreach($results[$job]['includes'] as $i)
	{
		$i = str_replace("'",'',$i);
		$i = str_replace('"','',$i);
		
		foreach($results[$job]['ignorelist'] as $elem) $i = str_replace($elem,'',$i);
		
		if (stristr($i,'$'))
		{ 
			$results[$job]['dynamicincludes'][] = $i;
		}
		else
		{ 			
			if (! array_key_exists($i, $results) && !in_array($i,$joblist)) $joblist[] = $i;
			$filetree[$job][] = array('id'=>$i, 'parentid'=>$job,'label'=>'<a href="sofadoc.php?file='.$i.'">'.$i.'</a>');
			$results[$job]['includeslinks'][$i] = '<a href="sofadoc.php?file='.$i.'">'.$i.'</a>';			
		}
	}	

}

// input

$file = $_GET['file'] ?: '';
if (!$file)
{
	// show map
	
	echo '<h1>SofaDoc Map</h1>';
	
	echo '<table><tr><td width=33% valign=top>';
	
	echo '<h4>Classes</h4>';
	
	$tree = swCreateTree($classtree, $classtree[0]);
	echo swPrintTree($tree);
	
	echo '</td><td width=33% valign=top>';
	
	echo '<h4>Functions</h4>';
	
	ksort($functionlinks);
	echo '<ul>';
	foreach($functionlinks as $f)
	{
		echo '<li>'.$f.'</li>';
	}
	echo '</ul>';
	
	echo '</td><td width=33% valign=top>';
	
	echo '<h4>Files</h4>';
	
	$tree = swCreateTree($filetree, $filetree[0]);
	echo swPrintTree($tree);
	
	echo '</td></tr></table>';
		
	echo '</body></html>';
	
	return;
}


// show result for one file 
$result = $results[$file];

echo '<h1>'.$file.'</h1>';

if (count($result['comments'])) echo '<p>'.swPrintComment($result['comments'][0]); // use only first;


// clean up others
foreach($result['others'] as $other)
{
	if (isset($classlinks[$other]))
	{
		$result['usedclasses'][] = $other;
		$result['others'] = array_diff($result['others'],array($other));
	}
	elseif(isset($functionlinks[$other]))
	{
		$result['usedfunctions'][] = $other;
		$result['others'] = array_diff($result['others'],array($other));
	}
	else
	{		
		foreach($results as $methodfiles)
		{
			foreach($methodfiles['methods'] as $methods)
			{				
				foreach($methods as $method)			
				{
					if ($other == $method[0]) $result['others'] = array_diff($result['others'],array($other));
				}
			}
			foreach($methodfiles['properties'] as $properties)
			{
					if ('$'.$other == $properties[0]) $result['others'] = array_diff($result['others'],array($other));
			}
		}
	}
}

if (count($result['classes']))
{
	echo '<h4>Classes</h4>';
	$printlines = [];
	foreach ($result['classes'] as $class)
	{
		if ($class[1] == 'extends')
		{
			$printlines[] = '<a name="'.$class[0].'">'.$class[0].' extends '.$classlinks[$class[2]].' '.swPrintComment($classcomment[$class[0]]);
		}
		else
		{
			$printlines[] = '<a name="'.$class[0].'">'.$class[0].' '.swPrintComment($classcomment[$class[0]]);
		}
		
		$methodlines = [];
		$methodlines[] = '<ul>';
		
		foreach($result['methods'][$class[0]] as $method)
		{
			$methodlines[] = '<li>'.join('',$method).' '.swPrintComment($methodcomment[$class[0]][$method[0]]).'</li>';
		}
		$methodlines[] = '</ul>';
		$methodlines[] = '<ul>';
		foreach($result['properties'][$class[0]] as $property)
		{
			$methodlines[] = '<li>'.$property.'</li>';
		}
		$methodlines[] = '</ul>';
		$printlines[] = join('',$methodlines);
	}
	echo '<p>'.join('<br>',$printlines);
}

if (count($result['functions']))
{
	echo '<h4>Functions</h4>';
	$printlines = [];
	foreach ($result['functions'] as $function)
	{
		$printlines[] = '<a name="'.$function[0].'">'.join('',$function).' '.swPrintComment($functioncomment[$function[0]]);
	}
	echo '<p>'.join('<br>',$printlines);
}

if (count($result['usedclasses']))
{
	echo  '<h4>Used classes</h4>';
	
	$lines = [];
	
	foreach($result['usedclasses'] as $c)
	{
	  	if (isset($classlinks[$c]))
	  	{
		  	$lines[] = $classlinks[$c];		
		}
		else
		{
			$lines[] = $c;
		}	  		
	}	
	echo '<p>'.join(', ',$lines);	
}


if (count($result['usedfunctions']))
{
	echo '<h4>Used functions</h4>';
	echo '<p>';
	$lines = [];
	foreach ($result['usedfunctions'] as $f)
	{
		if (isset($functionlinks[$f]))
	  	{
		  	$lines[] = $functionlinks[$f];		
		}
		else
		{
			$lines[] = $f;
		}
	}	
	echo '<p>'.join(', ',$lines);
} 


$result['usedglobals'] = array_unique($result['usedglobals']);
sort($result['usedglobals']);
if (count($result['usedglobals'])) echo '<h4>Used globals</h4><p>'.join(', ',$result['usedglobals']);

if (count($result['variables'])) 
{
	echo '<h4>Variables</h4>';
	
	$lines = [];
	foreach($result['variables'] as $v)
	{
		if (strlen($v)>3)
		{
			$lines[] = 	$v;
		}
		else
		{
			$shortvariables[] = $v;
		}
	}
	echo join(', ',$lines);	
	if (count($shortvariables)) echo '<p>Short variables: '. join(', ',$shortvariables);
}

if (count($result['includes'])) 
{
	echo '<h4>Includes</h4>';
	echo '<ul>';
	foreach($result['includeslinks'] as $k=>$v)
	{
		echo '<li>'.$v.' '.$includescomments[$k].'</li>';
	}
	echo '</ul>';
	if (count($result['dynamicincludes'])) echo '<p>Dynamic includes: '.join(', ',$result['dynamicincludes']);
}


if (count($result['features'])) echo  '<h4>Features</h4><p>'.join(', ',$result['features']);

if (count($result['others'])) echo '<h4>Others</h4><p>'.join(', ',$result['others']);

if (count($result['tokens'])) echo  '<h4>Tokens</h4><p>'.join(', ',$result['tokens']);

	
