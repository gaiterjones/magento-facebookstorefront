<?php
/**
 *
 *  Copyright (C) 2016 paj@gaiterjones.com
 *
 *
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento HTML class.
 *
 */
class HTML {


		function getFirstSentence($content) {

	    $content = str_ireplace('<br />', ' - ', $content);
	    $content = html_entity_decode(strip_tags($content));
	    $pos = strpos($content, '.');

	    if($pos === false) {
	        return htmlentities($content);
	    }
	    else {
	        return htmlentities(substr($content, 0, $pos+1));
	    }

	}



/**
		 * clean_up function.
		 *
		 * @access private
		 * @param mixed $text
		 * @return void
		 */
		function clean_up ($text)
		{
			$cleanText=self::replaceHtmlBreaks($text," ");
			$cleanText=self::strip_html_tags($cleanText);
			$cleanText=preg_replace("/&#?[a-z0-9]+;/i"," ",$cleanText);
			$cleanText=htmlspecialchars($cleanText);

			return $cleanText;
		}

		/**
		 * strip_html_tags function.
		 *
		 * @access private
		 * @param mixed $text
		 * @return void
		 */
		function strip_html_tags( $text )
		{
		    $text = preg_replace(
		        array(
		          // Remove invisible content
		            '@<head[^>]*?>.*?</head>@siu',
		            '@<style[^>]*?>.*?</style>@siu',
		            '@<script[^>]*?.*?</script>@siu',
		            '@<object[^>]*?.*?</object>@siu',
		            '@<embed[^>]*?.*?</embed>@siu',
		            '@<applet[^>]*?.*?</applet>@siu',
		            '@<noframes[^>]*?.*?</noframes>@siu',
		            '@<noscript[^>]*?.*?</noscript>@siu',
		            '@<noembed[^>]*?.*?</noembed>@siu',
		          // Add line breaks before and after blocks
		            '@</?((address)|(blockquote)|(center)|(del))@iu',
		            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
		            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
		            '@</?((table)|(th)|(td)|(caption))@iu',
		            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
		            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
		            '@</?((frameset)|(frame)|(iframe))@iu',
		        ),
		        array(
		            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
		            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
		            "\n\$0", "\n\$0",
		        ),
		        $text );
		    return strip_tags( $text );
		}

		/**
		 * replaceHtmlBreaks function.
		 *
		 * @access private
		 * @param mixed $str
		 * @param mixed $replace
		 * @param mixed $multiIstance (default: FALSE)
		 * @return void
		 */
		function replaceHtmlBreaks($str, $replace, $multiIstance = FALSE)
		{

		    $base = '<[bB][rR][\s]*[/]*[\s]*>';

		    $pattern = '|' . $base . '|';

		    if ($multiIstance === TRUE) {
		        //The pipe (|) delimiter can be changed, if necessary.

		        $pattern = '|([\s]*' . $base . '[\s]*)+|';
		    }

		    return preg_replace($pattern, $replace, $str);
		}

}
?>
