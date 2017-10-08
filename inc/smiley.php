<?php  
/**
 * Smiley Javascript
 *
 * Returns the javascript required for the smiley insertion.  Optionally takes
 * an array of aliases to loosely couple the smiley array to the view.
 *
 * @access	public
 * @param	mixed	alias name or array of alias->field_id pairs
 * @param	string	field_id if alias name was passed in
 * @return	array
 */
function smiley_js($alias = '', $field_id = '', $inline = TRUE)
{
	static $do_setup = TRUE;

	$r = '';

	if ($alias != '' && ! is_array($alias))
	{
		$alias = array($alias => $field_id);
	}

	if ($do_setup === TRUE)
	{
			$do_setup = FALSE;

			$m = array();

			if (is_array($alias))
			{
				foreach ($alias as $name => $id)
				{
					$m[] = '"'.$name.'" : "'.$id.'"';
				}
			}

			$m = '{'.implode(',', $m).'}';

			$r .= <<<EOF
			var smiley_map = {$m};

			function insert_smiley(smiley, field_id) {
				var el = document.getElementById(field_id), newStart;

				if ( ! el && smiley_map[field_id]) {
					el = document.getElementById(smiley_map[field_id]);

					if ( ! el)
						return false;
				}

				el.focus();
				smiley = " " + smiley;

				if ('selectionStart' in el) {
					newStart = el.selectionStart + smiley.length;

					el.value = el.value.substr(0, el.selectionStart) +
									smiley +
									el.value.substr(el.selectionEnd, el.value.length);
					el.setSelectionRange(newStart, newStart);
				}
				else if (document.selection) {
					document.selection.createRange().text = smiley;
				}
			}
EOF;
	}
	else
	{
		if (is_array($alias))
		{
			foreach ($alias as $name => $id)
			{
				$r .= 'smiley_map["'.$name.'"] = "'.$id.'";'."\n";
			}
		}
	}

	if ($inline)
	{
		return '<script type="text/javascript" charset="utf-8">/*<![CDATA[ */'.$r.'// ]]></script>';
	}
	else
	{
		return $r;
	}
}


// ------------------------------------------------------------------------

/**
 * Get Clickable Smileys
 *
 * Returns an array of image tag links that can be clicked to be inserted
 * into a form field.
 *
 * @access	public
 * @param	string	the URL to the folder containing the smiley images
 * @return	array
 */
function get_clickable_smileys($image_url, $alias = '', $smileys = NULL)
{
	// For backward compatibility with js_insert_smiley

	if (is_array($alias))
	{
		$smileys = $alias;
	}

	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $smileys;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = rtrim($image_url, '/').'/';

	$used = array();
	foreach ($smileys as $key => $val)
	{
		// Keep duplicates from being used, which can happen if the
		// mapping array contains multiple identical replacements.  For example:
		// :-) and :) might be replaced with the same image so both smileys
		// will be in the array.
		if (isset($used[$smileys[$key][0]]))
		{
			continue;
		}

		$link[] = "<a href=\"javascript:void(0);\" onclick=\"insert_smiley('".$key."', '".$alias."')\" title=\"".$smileys[$key][3]."\"><img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" style=\"border:0;\" /></a>";

		$used[$smileys[$key][0]] = TRUE;
	}

	return $link;
}


/**
 * Parse Smileys
 *
 * Takes a string as input and swaps any contained smileys for the actual image
 *
 * @access	public
 * @param	string	the text to be parsed
 * @param	string	the URL to the folder containing the smiley images
 * @return	string
 */
function parse_smileys($str = '', $image_url = '', $smileys = NULL)
{
	if ($image_url == '')
	{
		return $str;
	}

	if ( ! is_array($smileys))
	{
		if (FALSE === ($smileys = _get_smiley_array()))
		{
			return $str;
		}
	}

	// Add a trailing slash to the file path if needed
	$image_url = preg_replace("/(.+?)\/*$/", "\\1/",  $image_url);

	foreach ($smileys as $key => $val)
	{
		$str = str_replace($key, "<img src=\"".$image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" style=\"border:0;\" />", $str);
	}

	return $str;
}


/**
 * Get Smiley Array
 *
 * Fetches the config/smiley.php file
 *
 * @access	private
 * @return	mixed
 */

function _get_smiley_array()
{
	// if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/smileys.php'))
	// {
	//     include(APPPATH.'config/'.ENVIRONMENT.'/smileys.php');
	// }
	// elseif (file_exists('config/smileys.php'))
	// {
	// 	include('config/smileys.php');
	// }
	
	// if (isset($smileys) AND is_array($smileys))
	// {
	// 	return $smileys;
	// }

	// return FALSE;

	return array(
	//	smiley			image name						width	height	alt
		':-)'			=>	array('grin.gif',			'19',	'19',	'grin'),
		':lol:'			=>	array('lol.gif',			'19',	'19',	'LOL'),
		':cheese:'		=>	array('cheese.gif',			'19',	'19',	'cheese'),
		':)'			=>	array('smile.gif',			'19',	'19',	'smile'),
		';-)'			=>	array('wink.gif',			'19',	'19',	'wink'),
		';)'			=>	array('wink.gif',			'19',	'19',	'wink'),
		':smirk:'		=>	array('smirk.gif',			'19',	'19',	'smirk'),
		':roll:'		=>	array('rolleyes.gif',		'19',	'19',	'rolleyes'),
		':-S'			=>	array('confused.gif',		'19',	'19',	'confused'),
		':wow:'			=>	array('surprise.gif',		'19',	'19',	'surprised'),
		':bug:'			=>	array('bigsurprise.gif',	'19',	'19',	'big surprise'),
		':-P'			=>	array('tongue_laugh.gif',	'19',	'19',	'tongue laugh'),
		'%-P'			=>	array('tongue_rolleye.gif',	'19',	'19',	'tongue rolleye'),
		';-P'			=>	array('tongue_wink.gif',	'19',	'19',	'tongue wink'),
		':P'			=>	array('raspberry.gif',		'19',	'19',	'raspberry'),
		':blank:'		=>	array('blank.gif',			'19',	'19',	'blank stare'),
		':long:'		=>	array('longface.gif',		'19',	'19',	'long face'),
		':ohh:'			=>	array('ohh.gif',			'19',	'19',	'ohh'),
		':grrr:'		=>	array('grrr.gif',			'19',	'19',	'grrr'),
		':gulp:'		=>	array('gulp.gif',			'19',	'19',	'gulp'),
		'8-/'			=>	array('ohoh.gif',			'19',	'19',	'oh oh'),
		':down:'		=>	array('downer.gif',			'19',	'19',	'downer'),
		':red:'			=>	array('embarrassed.gif',	'19',	'19',	'red face'),
		':sick:'		=>	array('sick.gif',			'19',	'19',	'sick'),
		':shut:'		=>	array('shuteye.gif',		'19',	'19',	'shut eye'),
		':-/'			=>	array('hmm.gif',			'19',	'19',	'hmmm'),
		'>:('			=>	array('mad.gif',			'19',	'19',	'mad'),
		':mad:'			=>	array('mad.gif',			'19',	'19',	'mad'),
		'>:-('			=>	array('angry.gif',			'19',	'19',	'angry'),
		':angry:'		=>	array('angry.gif',			'19',	'19',	'angry'),
		':zip:'			=>	array('zip.gif',			'19',	'19',	'zipper'),
		':kiss:'		=>	array('kiss.gif',			'19',	'19',	'kiss'),
		':ahhh:'		=>	array('shock.gif',			'19',	'19',	'shock'),
		':coolsmile:'	=>	array('shade_smile.gif',	'19',	'19',	'cool smile'),
		':coolsmirk:'	=>	array('shade_smirk.gif',	'19',	'19',	'cool smirk'),
		':coolgrin:'	=>	array('shade_grin.gif',		'19',	'19',	'cool grin'),
		':coolhmm:'		=>	array('shade_hmm.gif',		'19',	'19',	'cool hmm'),
		':coolmad:'		=>	array('shade_mad.gif',		'19',	'19',	'cool mad'),
		':coolcheese:'	=>	array('shade_cheese.gif',	'19',	'19',	'cool cheese'),
		':vampire:'		=>	array('vampire.gif',		'19',	'19',	'vampire'),
		':snake:'		=>	array('snake.gif',			'19',	'19',	'snake'),
		':exclaim:'		=>	array('exclaim.gif',		'19',	'19',	'excaim'),
		':question:'	=>	array('question.gif',		'19',	'19',	'question') // no comma after last item
	);
}

/**
 * JS Insert Smiley
 *
 * Generates the javascript function needed to insert smileys into a form field
 *
 * DEPRECATED as of version 1.7.2, use smiley_js instead
 *
 * @access	public
 * @param	string	form name
 * @param	string	field name
 * @return	string
 */

function js_insert_smiley($form_name = '', $form_field = '')
{
	return <<<EOF
	<script type="text/javascript">
	function insert_smiley(smiley)
	{
		document.{$form_name}.{$form_field}.value += " " + smiley;
	}
	</script>
EOF;
}

/**
 * Set columns.  Takes a one-dimensional array as input and creates
 * a multi-dimensional array with a depth equal to the number of
 * columns.  This allows a single array with many elements to  be
 * displayed in a table that has a fixed column count.
 *
 * @access	public
 * @param	array
 * @param	int
 * @return	void
 */
function make_columns($array = array(), $col_limit = 0)
{
	if ( ! is_array($array) OR count($array) == 0)
	{
		return FALSE;
	}

	if ($col_limit == 0)
	{
		return $array;
	}

	$new = array();
	while (count($array) > 0)
	{
		$temp = array_splice($array, 0, $col_limit);

		if (count($temp) < $col_limit)
		{
			for ($i = count($temp); $i < $col_limit; $i++)
			{
				$temp[] = '&nbsp;';
			}
		}

		$new[] = $temp;
	}

	return $new;
}