<?php

function render_template($templateName, $title=null, $columns=null, $data=null){
	global $Sql, $View;
	// se puso aca para que sea global para todas las secciones
	if( isset($_POST['excel']) ){
		$excel = array();
		$header = array();
		foreach( $columns as $k=>$v ){
			if( $v[1] == 'boolean' ){
				if( $data!=null && is_array($data) ){
					foreach( $data as $id=>$row ){
						$data[$id][$v[0]] = $row[$v[0]] == '1' ? 'Si' : 'No'; // expresiones booleanas en espaN?ol;
					}
				}
			}
			if( !in_array($v[1],array('hidden','index')) ){
				$excel[0][] = $v[0];
				$header[] = html_entity_decode($v[2]);
			}
		}
		foreach( $data as $k=>$v ){
			$line = array();
			foreach( $excel[0] as $name ){
				$line[] = $v[$name];
			}
			$excel[] = $line;
		}
		$excel[0] = $header;
		date_default_timezone_set('Chile/Continental');
		require_once('inc/PHPExcel/PHPExcel.php');

		$doc = new PHPExcel();
		$doc->setActiveSheetIndex(0);

		$doc->getActiveSheet()->fromArray($excel, null, 'A1');
		$lastCol = $doc->getActiveSheet()->getHighestColumn();
		foreach( range('A',$lastCol) as $col ){
			$doc->getActiveSheet()->getColumnDimension($col)
				->setAutoSize(true);  // ajustamos el ancho de las columnas automaticamente
		}
		$doc->getActiveSheet()->calculateColumnWidths();
		$doc->getActiveSheet()->freezePane('A2'); // dejamos fija la primera columna del excel
		$doc->getActiveSheet()->getStyle("A1:".$lastCol."1")->getFont()->setBold(true);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$title.'.xls"');
		header('Cache-Control: max-age=0');

		// Do your stuff here
		$writer = PHPExcel_IOFactory::createWriter($doc, 'Excel5');

		$writer->save('php://output');
		exit;
	}

	$View->setPath('templates');
	$View->setTemplate($templateName);

	if( $title!=null ){
		$View->set('title',$title);
	}
	$idField = null;
	if( $columns!=null ){
		foreach( $columns as $index => $col ){
			if( @$col[1]=='index' ){
				$idField = $col[0];
				$col[1] = "hidden";
			}
			if( gettype(@$col[3])=='boolean' ){
				$col[3] = array("required" => $col[3]);
			}
			if( preg_match("/([a-zA-Z]+)\[([-_ \.;&!<>,a-zñáéíóúA-ZÑÁÉÍÓÚ0-9=\|'%()]+)\]/",$col[1],$matches) ){ // acepta cosas como "select[Si=1|No=0]" o "select[table=persona|index=id|label=nombre|where=id=100]".
				$type = $matches[1];
				$tmp = explode('|',$matches[2]);
				$options = array();
				foreach( $tmp  as $option ){
					$optionArray = explode('=',$option,2); // en caso de tener 'where', le indicamos q solo tome el primer "=", para eso se le pasa el parametro 'limite' a explode.
					$options[$optionArray[0]] = @$optionArray[1];
				}
				if( isset($col[3]['foreign']) && $col[3]['foreign'] ){
					$table = $options['table'];
					$value = $options['index'];
                    if( isset($options['label']) ){
                        $label = $options['label'];
                    }
					if( empty($label) || $label==null ){
						$label = $value;
					}
					// where puede ser no pasado
					$where = "1=1";
					if( isset($options['where']) ){
						$where = $options['where'];
					}
					$options = array();
					if( !empty($col[3]['grouped']) ){
						$group = $col[3]['grouped'];
						$q="SELECT $label,$value,$group FROM $table WHERE $where;";
					} else {
						$q="SELECT $label,$value FROM $table WHERE $where;";
					}
					if( $rows = $Sql->q_read($q) ){
						// HACK: en caso de que tengan el formato tabla.columna, debemos sacar la tabla.
						$tmp = explode('.',$label);
						$i = (count($tmp)-1); // si no tenia punto, va a devolver un arreglo con un solo elemento, si no con 2, y necesitamos el ultimo q es lo q esta despues del punto.
						$label = $tmp[$i];
						$tmp = explode('.',$value);
						$i = (count($tmp)-1);
						$value = $tmp[$i];
						// FIN HACK
						foreach( $rows as $row ){
							if( !empty($col[3]['grouped']) ){
								$options[$row[$col[3]['grouped']]][$row[$label]] = $row[$value];
							} else {
								$options[$row[$label]] = $row[$value];
							}
						}
					} else {
						if( isset($_REQUEST['debug']) ){
							var_dump($rows);
							exit;
						}
					}
				}
				$col[1] = array(
					$type,
					$options
				);
			}
			$columns[$index] = $col;
		}
		$View->set('columns',$columns);
	}
	if( $data!=null ){
		if( is_array($columns) ){
			foreach( $columns as $column ){
				// en el caso de los datos foraneos, aca los seteamos para que se lea correctamente.
				$field = $column[0];
				$values = @$column[1][1];
				if( is_array($values) ){
					// 'values' es arreglo solo si es un dato foraneo
					foreach( $values as $label=>$index ){
						foreach( $data as $i=>$row ){
							// si la llave o 'index' es igual al que esta en los datos, lo asignamos como valor
							if( isset($row[$field]) && $row[$field]==$index ){
								$row[$field] = $label;
								$row[$field."-index"] = $index;
							}
							$data[$i] = $row;
						}
					}
				}
			}
		}
		/* Ordenamos los datos para que la llave de estos sea la id de la tabla */
		if( isset($idField) && $idField!=null ){
			$tmp = array();
			foreach($data as $k=>$v){
				if( !is_array($v) ){
					$tmp[0] = $data;
					break;
				}
				$tmp[$v[$idField]] = $v;
			}
			$data = $tmp;
		}
		$View->set('data',$data);
	}

	$content = $View->getView();
	$View->set('CONTENT',$content);

	$footer=$Sql->q_read("SELECT * FROM texto WHERE idTipo=2");
	if (count($footer)){
		$View->set('SMALLFOOTER','800 367 482');
		$View->set('FOOTER',urldecode($footer[0]['cuerpo']));
	}
	$View->setTemplate('index.html');
	return $View->getView();
}
function compile_error($msg){
	global $view;
	$view->set("BODY",$msg);
	$view->setTemplate("error.html");
	return $view->getView();
}
function getLogoBase64(){
	$path = PATH.'/html/assets/logo.png';
	$type = pathinfo($path, PATHINFO_EXTENSION);
	$data = file_get_contents($path);
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	return $base64;
}

function sendEmail($to, $title, $htmlBody, $attachments=null){
    global $configs;
	error_reporting(~E_ALL);
	require_once(PROJECT_FOLDER.'inc/swift/swift_required.php');
	$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
		->setUsername('sdbsantarita@gmail.com')
	  	->setPassword('nMlgp.Ytp-67Rt');
	$mailer = Swift_Mailer::newInstance($transport);
	$message = Swift_Message::newInstance($title)
	  	->setFrom(array('sdbsantarita@gmail.com' => 'SDB.CL - Selección de Bodega'))
	  	->setTo(array($to))
		->setContentType("text/html")
	  	->setBody($htmlBody, 'text/html');
	if( $attachments!=null && is_array($attachments) ){
		foreach( $attachments as $name=>$file ){
			$message->attach(
				Swift_Attachment::fromPath($file)->setFilename($name)
			);
		}	
	}

	return $result = $mailer->send($message);
}
function getPage($columns,$table,$condition=null){
	global $Sql,$View;
	if( !isset($_REQUEST['excel']) ){
		$limit = null;
		$page = 0;
		if( !isset($_REQUEST['porPagina']) ){
			$limit="10";
		} else {
			$limit = (int)$_REQUEST['porPagina'];
		}
		if( !isset($_REQUEST['pagina']) ){
			$page = 1;
		} else {
			$page = (int)$_REQUEST['pagina'];
		}
		$offset = $limit*($page-1);
		/* CANTIDAD DE PAGINAS DISPONIBLES */
		$query = "SELECT COUNT(1) as total FROM $table";
		if( $condition!=null ){
			$query.=" WHERE $condition;";
		} else {
			$query.=";";
		}
		$pages = $Sql->q_read($query);
		$totalElements = $pages[0]['total'];
		$pages = ceil($totalElements/$limit);
		$maxPages = $pages;
		$tmp = array();
		echo "<br/>\n";
		if( $page==1 ){
			$limSup = 6;
			$limInf = 1;
		} elseif( $page==$pages ){
			$limSup = $pages;
			$limInf = $pages-6;
		} else {
			$limSup = $page+5 > $pages ? $pages : $page+5;
			$limInf = $page-5 < 1 ? 1 : $page-5;
		}
		for( $i=0;$i<$pages;$i++ ){
			if( $i+1>=$limInf && $i+1<=$limSup ){
				$cur = $i==($page-1);
				$tmp[] = array(
					"value" => ($i+1),
					"current" => $cur
				);
			}
		}
		if( count($tmp)<$pages ){
			if( $page>=7 ){
				$tmp = array_merge(array(array('value'=>1,'current'=>0),array('value'=>'...','current'=>0)),$tmp);
			}
			if( $page<=$pages-6 ){
				$tmp = array_merge($tmp,array(array('value'=>'...','current'=>0),array('value'=>$pages,'current'=>0)));
			}
		}
		$pages = $tmp;
		//print_r($pages);exit;
		$View->set("pagina","$page");
		$View->set("pages",$pages);
		$View->set("porPagina",$limit);
		// PREV
		if( $page>1 ){
			$View->set('PREV',true);
		}
		// NEXT
		if( $page<$maxPages ){
			$View->set('NEXT',true);
		}
		$pagination = array($offset,$limit);
	}
	/* SE DEVUELVE SOLO LOS LIMITES PARA USARLOS EN EL SELECT */
	$query = "SELECT * FROM $table";
	if( $condition!=null ){
		$query.=" WHERE $condition";
	}
	if( isset($pagination) ){
		$query .=" LIMIT ?,?;";
	} else {
		$pagination = null;
	}
    exit($query);
	$cur = $Sql->q_read($query,$pagination);
	return $cur;
}
function url_slug($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);
	// Merge options
	$options = array_merge($defaults, $options);
	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
		'ß' => 'ss',
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
		'ÿ' => 'y',
		// Latin symbols
		'©' => '(c)',
		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
		'Ž' => 'Z',
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z',
		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
		'Ż' => 'Z',
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
	);
	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	// Transliterate characters to ASCII
	if ($options['transliterate']) {
        $str = str_replace(array_keys($char_map), $char_map, $str);
	}
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}
function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    $rgb = array($r, $g, $b);
    //return implode(",", $rgb); // returns the rgb values separated by commas
    return $rgb; // returns an array with the rgb values
}
?>
