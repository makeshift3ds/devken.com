<?

//		XLS Reader Class
//		(c) 2004 Paggard [paggard@paggard.com]
//		03 july 2005 build 40

//--------------------------
// Header File XLS Reader
//--------------------------
define('EXCEL_BLOCK_SIZE', 0x200);
define('THIS_BLOCK_SIZE', 0x80);


// --- WORK BOOK RECORDS ----------------------------------
define('RCRD_BOF', 0x0809);
define('RCRD_BOUNDSHEET', 0x0085);
define('RCRD_CONTINUE', 0x003C);
define('RCRD_DATEMODE', 0x0022);
define('RCRD_EOF', 0x000A);
define('RCRD_EXTERNCOUNT', 0x0016);
define('RCRD_EXTERNSHEET', 0x0017);
define('RCRD_SST', 0x00FC);
define('RCRD_STYLE', 0x0293);
define('RCRD_WINDOW1', 0x003D);
define('RCRD_WINDOW2', 0x023E);

// --- WORK SHEET RECORDS ---------------------------------
define('RCRD_BLANK', 0x0201);
define('RCRD_BOTTOMMARGIN', 0x0029);
define('RCRD_COLINFO', 0x007D);
define('RCRD_DEFCOLWIDTH', 0x0055);
define('RCRD_DIMENSIONS', 0x0200);
//define('RCRD_EXTERNCOUNT', 0x0016);
//define('RCRD_EXTERNSHEET', 0x0017);
define('RCRD_FOOTER', 0x0015);
define('RCRD_FORMAT', 0x041e);
define('RCRD_FORMULA', 0x0006);
define('RCRD_HCENTER', 0x0083);
define('RCRD_HEADER', 0x0014);
define('RCRD_HLINK', 0x01B8);
define('RCRD_LABEL', 0x0204);
define('RCRD_LABELSST', 0x00FD); // replacement of LABEL Record in BIFF 8
define('RCRD_LEFTMARGIN', 0x0026);
define('RCRD_MERGEDCELLS', 0x00E5);
define('RCRD_MULRK', 0x00BD); // Multiple RK
define('RCRD_NAME', 0x0018);
define('RCRD_NUMBER', 0x0203);
define('RCRD_PALETTE', 0x0092);
define('RCRD_PASSWORD', 0x0013);
define('RCRD_PRINTGRIDLINES', 0x002b);
define('RCRD_PRINTHEADERS', 0x002a);
define('RCRD_PROTECT', 0x0012);
define('RCRD_RIGHTMARGIN', 0x0027);
define('RCRD_RK', 0x027E);
define('RCRD_ROW', 0x0208);
define('RCRD_SCL', 0x00A0);
define('RCRD_SELECTION', 0x001D);
define('RCRD_SETUP', 0x00A1);
define('RCRD_STRING', 0x0207);
define('RCRD_TOPMARGIN', 0x0028);
define('RCRD_VCENTER', 0x0084);
define('RCRD_WSBOOL', 0x0081);




// --- WORK FORMAT RECORDS --------------------------------
define('RCRD_FONT', 0x31);
define('RCRD_XF', 0x00E0);

// --- FORMAT SUPPORT DEFINES
define('XF_SCRIPT_NONE',0);
define('XF_SCRIPT_SUP',1);
define('XF_SCRIPT_SUB',2);

define('XF_UL_NONE',0x0);
define('XF_UL_SINGLE',0x1);
define('XF_UL_DOUBLE',0x2);
define('XF_UL_SINGLE_ACC',0x3);
define('XF_UL_DOUBLE_ACC',0x4);

define('XF_STYLE_ITALIC', 0x2);
define('XF_STYLE_STRIKEOUT', 0x8);

define('XF_WGHT_REGULAR',0x190);
define('XF_WGHT_BOLD',0x2BC);

//-------------------------------------------------------------------------------------------------
// support functions

// ------------------------------------------------------------------------------------------------
// convert UNIX time_stamp to Excel time number
	function Unix2Excel($time=false) {
		$UNIX_Start = 25569.125;
		$unix = ($time) ? $time : time();
		$days = floor($unix / 86400);
		$seconds = ($unix - ($days * 86400));
		$excel = $days + $UNIX_Start + ((round((999999 / 86400) * $seconds)) * 0.000001);
		return $excel;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_long_from_string($string) {
		return ord($string[0])+256*(ord($string[1])+256*(ord($string[2])+256*(ord($string[3]))));
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function getUnicodeString(&$str,$ofs,$length=false){
		if ($length) {
			if ($length == -1) {
				$length = ord($str[$ofs]);
				$alter = unicode2ascii(substr($str,$ofs+3,$length));
				return $alter;
			}
			$bstring = "";
			$index = $ofs + 1;
			for ($i=0;$i<$length;$i++) {
				$bstring = $bstring.$str[$index];
				$index += 2;
			}
			return substr($bstring,0,$length);
		}
		$size = 0;
		$i_ofs = 0;

		$size = ord($str[$ofs]);
		$i_ofs = 1;
		$alter = substr($str,$ofs+$i_ofs+1,$size);
		$alter = unicode2ascii($alter);
		return $alter;
		//return substr($str,$ofs+$i_ofs+1,$size);
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function getByteString(&$str,$ofs){
		$size=0;
		$i_ofs=0;
		$size=ord($str[$ofs]);
		$i_ofs=1;
		return substr($str,$ofs+$i_ofs+1,$size);
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function unicode2ascii($str) {
		$stack = "";
		for ($i=0;$i < strlen($str);$i++) {
			$c_char = $str[$i];
			if (ord($c_char) > 1) {
				$stack .= $c_char;
			}
		}
		return $stack;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function unicode2html($str) {
		$stack = "";
		if (!$str || strlen($str)==0) {
			return "";
		}
		for ($i=0;$i < strlen($str)/2;$i++) {
			@$charcode = @ord(@$str[@$i*2])+256*@ord(@$str[@$i*2+1]);
			if ($charcode == 0) {
				$cur = substr($str,$i*2,2);
				$stack .= $cur;
			}
			else {$stack .= "&#".$charcode.";";}
		}
		return $stack;
	} // end of function
// ------------------------------------------------------------------------------------------------

?>
