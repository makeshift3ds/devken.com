<?

//		XLS Reader Class
//		(c) 2005 Paggard [paggard@paggard.com]
//		03 july 2005 build 40

// WORKBOOK STRUCTURE
// ------------------------------------------------------------------------------------------------
class struct_book {

	var $date_mode;
	var $sheets = array();
	var $_xf = array();
	var $_fonts = array();
	var $_palette = array();
	var $_num_formats = array();
	var $_bi_num_formats = array(); // build in
	var $_cell_data = array();

	var $_cur_sheet_num = false;
	var $_cur_sheet = false;

	//----------------------------------
	var $border = 1;
	var $cellpadding = 3;
	var $cellspacing = 0;

	var $mm2pix_rate = 6.5;
	var $draw_index = true;
	var $draw_sheet_list = true;
	var $draw_sheet_name = true; // shet names are in UNICODE
	var $bg_color = "white";
	var $month_names;
	var $month_names_short;
	var $overr_int_align = false;
	var $overr_double_align = false;

	var $overr_align = false;
	var $overr_valign = false;
	var $overr_bgcolor = false;
	var $overr_width = false;

	var $overr_fnt_all = false; // overrides all font settings stored in XLS
	var $overr_fnt_color = false;
	var $overr_fnt_bold = false;
	var $overr_fnt_size = false;
	var $overr_fnt_name = false;
	var $overr_fnt_itallic = false;
	var $overr_fnt_underline = false;
	var $overr_fnt_script = false;


// ------------------------------------------------------------------------------------------------
// CONSTRUCTOR
	function struct_book() {
		include "xls_reader_conf.inc";
		// - set variables --------------
		$this->draw_sheet_name = (isset($DrawSheetName)) ? $DrawSheetName : $this->draw_sheet_name;
		$this->draw_sheet_list = (isset($DrawSheetsList)) ? $DrawSheetsList : $this->draw_sheet_list;
		$this->border = (isset($border)) ? $border : $this->border;
		$this->cellpadding = (isset($cellpadding)) ? $cellpadding : $this->cellpadding;
		$this->cellspacing = (isset($cellspacing)) ? $cellspacing : $this->cellspacing;
		$this->mm2pix_rate = (isset($Width2PixelsRate)) ? $Width2PixelsRate : $this->mm2pix_rate;
		$this->draw_index = (isset($DrawIndex)) ? $DrawIndex : $this->draw_index;
		$this->bg_color = (isset($TableBgColor)) ? $TableBgColor : $this->bg_color;
		$this->overr_int_align = (isset($OverrideIntAlign)) ? $OverrideIntAlign : $this->overr_int_align;
		$this->overr_double_align = (isset($OverrideDoubleAlign)) ? $OverrideDoubleAlign : $this->overr_double_align;

		$this->overr_align = (isset($OverrideAlign)) ? $OverrideAlign : $this->overr_align;
		$this->overr_valign = (isset($OverrideVAlign)) ? $OverrideVAlign : $this->overr_valign;
		$this->overr_bgcolor = (isset($OverrideBgColor)) ? $OverrideBgColor : $this->overr_bgcolor;
		$this->overr_width = (isset($OverrideWidth)) ? $OverrideWidth : $this->overr_width;

		$this->overr_fnt_all = (isset($OverrideFontAll)) ? $OverrideFontAll : $this->overr_fnt_all;
		$this->overr_fnt_color = (isset($OverrideFontColor)) ? $OverrideFontColor : $this->overr_fnt_color;
		$this->overr_fnt_bold = (isset($OverrideFontBold)) ? $OverrideFontBold : $this->overr_fnt_bold;
		$this->overr_fnt_size = (isset($OverrideFontSize)) ? $OverrideFontSize : $this->overr_fnt_size;
		$this->overr_fnt_name = (isset($OverrideFontName)) ? $OverrideFontName : $this->overr_fnt_name;
		$this->overr_fnt_itallic = (isset($OverrideFontItallic)) ? $OverrideFontItallic : $this->overr_fnt_itallic;
		$this->overr_fnt_underline = (isset($OverrideFontUnderline)) ? $OverrideFontUnderline : $this->overr_fnt_underline;
		$this->overr_fnt_script = (isset($OverrideFontScript)) ? $OverrideFontScript : $this->overr_fnt_script;
		// ------------------------------
		$mn_flg = 0;
		if (isset($MonthsNames) && is_array($MonthsNames)) {
			$this->month_names = $MonthsNames;
		}
		else {$mn_flg+=1;}
		if (isset($MonthsNamesShort) && is_array($MonthsNamesShort)) {
			$this->month_names_short = $MonthsNamesShort;
		}
		else {$mn_flg+=2;}
		if ($mn_flg > 0) {$this->_fil_months_names($mn_flg);}
		$this->_fill_built_in_formats();
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _fill_built_in_formats() {
		$this->_bi_num_formats = array (
			"0" => "General",
			"1" => "0",
			"2" => "0.00",
			"3" => "#,##0",
			"4" => "#,##0.00",
			"5" => "\"\$\"#,##0_);(\"\$\"#,##0)",
			"6" => "\"\$\"#,##0_);[Red](\"\$\"#,##0)",
			"7" => "\"\$\"#,##0.00_);(\"\$\"#,##0.00)",
			"8" => "\"\$\"#,##0.00_);[Red](\"\$\"#,##0.00)",
			"9" => "0%",
			"10" => "0.00%",
			"11" => "0.00E+00",
			"12" => "# ?/?",
			"13" => "# ??/??",
			"14" => "M/D/YY",
			"15" => "D-MMM-YY",
			"16" => "D-MMM",
			"17" => "MMM-YY",
			"18" => "h:mm AM/PM",
			"19" => "h:mm:ss AM/PM",
			"20" => "h:mm",
			"21" => "h:mm:ss",
			"22" => "M/D/YY h:mm",
			"37" => "_(#,##0_);(#,##0)",
			"38" => "_(#,##0_);[Red](#,##0)",
			"39" => "_(#,##0.00_);(#,##0.00)",
			"40" => "_(#,##0.00_);[Red](#,##0.00)",
			"41" => "_(\"\$\"* #,##0_);_(\"\$\"* (#,##0);_(\"\$\"* "-"_);_(@_)",
			"42" => "_(* #,##0_);_(* (#,##0);_(* "-"_);_(@_)",
			"43" => "_(\"\$\"* #,##0.00_);_(\"\$\"* (#,##0.00);_(\"\$\"* "-"??_);_(@_)",
			"44" => "_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)",
			"45" => "mm:ss",
			"46" => "[h]:mm:ss",
			"47" => "mm:ss.0",
			"48" => "##0.0E+0",
			"49" => "@"
		);
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _fil_months_names($sl) {
		for ($m=0;$m<12;$m++) {
			$dt = mktime (0,0,0,$m+2,0,2004);
			switch ($sl) {
				case 1:
					$this->month_names[$m] = date("F",$dt);
					break;
				case 2:
					$this->month_names_short[$m] = date("M",$dt);
					break;
				case 3:
					$this->month_names[$m] = date("F",$dt);
					$this->month_names_short[$m] = date("M",$dt);
					break;
			}
			//$this->month_names[$m] = date("F",$dt);
			//$this->month_names_short[$m] = date("M",$dt);
		}
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _check_palette() {
		if (count($this->_palette)>0) {
			return;
		}
		$this->_palette = array (
			"000000","ffffff","ff0000","00ff00","0000ff","ffff00",
			"ff00ff","00ffff","800000","008000","000080","808000",
			"800080","008080","c0c0c0","808080","9999ff","993366",
			"ffffcc","ccffff","660066","ff8080","0066cc","ccccff",
			"000080","ff00ff","ffff00","00ffff","800080","800000",
			"008080","0000ff","00ccff","ccffff","ccffcc","ffff99",
			"99ccff","ff99cc","cc99ff","ffcc99","3366ff","33cccc",
			"99cc00","ffcc00","ff9900","ff6600","666699","969696",
			"003366","339966","003300","333300","993300","993366",
			"333399","333333"
		);
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function add_sheet() {
		$this->_cur_sheet_num = ($this->_cur_sheet_num===false) ? 0 : $this->_cur_sheet_num + 1;
		$this->sheets[$this->_cur_sheet_num] = new struct_sheet($this);
		$this->_cur_sheet =& $this->sheets[$this->_cur_sheet_num];
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_sheets_array() {
		for ($i=0;$i<count($this->sheets);$i++) {
			$f_ar[$i] = $this->sheets[$i]->name;
		}
		return $f_ar;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_sheet_html($sidx) {
		if (is_object($this->sheets[$sidx])) {
			return $this->sheets[$sidx]->get_html();
		}
		return "WorkSheet was not found by the given index (".$sidx.").";
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_sheet_raw($sidx) {
		if (is_object($this->sheets[$sidx])) {
			return $this->sheets[$sidx]->get_raw();
		}
		return "WorkSheet was not found by the given index (".$sidx.").";
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_sheets_list() {
		$fin = "";
		//$fin = "<a name=\"".strtoupper(preg_replace("/[\s\.,\-]+/","_",$this->name))."\">";
		$fin .= "<table ";
		$fin .= "border=".$this->border." ";
		$fin .= "cellpadding=".$this->cellpadding." ";
		$fin .= "cellspacing=".$this->cellspacing." ";
		$fin .= "class=\"xls_rd\" bgcolor=\"".$this->bg_color."\">\n";
		for ($i=0;$i<count($this->sheets);$i++) {
			$t_name = $this->sheets[$i]->name;
			if ($this->sheets[$i]->unicode) {
				$t_name = unicode2html($t_name);
			}
			$fin .= "<tr>";
			$fin .= "<td><a href=\"#".strtoupper(preg_replace("/[\s\.,\-]+/","_",$this->sheets[$i]->name))."\">".$t_name."</a></td>";
			$fin .= "</tr>";
		}
		$fin .= "</table>";
		return $fin;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_workbook_html() {
		$s_num = count($this->sheets);
		$fin = "";
		if ($this->draw_sheet_list) {
			$fin .= $this->get_sheets_list();
		}
		for ($i=0;$i<$s_num;$i++) {
			$fin .= $this->sheets[$i]->get_html();
		}
		return $fin;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_workbook_array() {
		$s_num = count($this->sheets);
		$fin = Array();
		for ($i=0;$i<$s_num;$i++) {
			$fin[] = $this->sheets[$i]->get_array();
		}
		return $fin;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_css_font($idx) {
		$stack = "";
		$fnt =& $this->_fonts[$idx];
		if (!$this->overr_fnt_color && isset($this->_palette[$fnt["color"]])) {
			$stack .= "color: #".$this->_palette[$fnt["color"]]."; ";
		}
		if (!$this->overr_fnt_bold) {
			if ($fnt["bold"]) {$tmp = "bold";}
			else {$tmp = "normal";}
		}
		if (!$this->overr_fnt_size) {
			if ($fnt["size"]) {$ft_size = $fnt["size"]."pt ";}
		}
		if ($tmp || $ft_size || !$this->overr_fnt_name) {
			$stack .= "font: ".$tmp." ".$ft_size." ".$fnt["name"]."; ";
		}
		if (!$this->overr_fnt_itallic) {
			if ($fnt["italic"]) {$stack .= "font-style: italic; ";}
		}
		if (!$this->overr_fnt_underline) {
			if ($fnt["underline"]) {$stack .= "text-decoration: underline;";}
		}
		if (!$this->overr_fnt_script) {
			switch ($fnt["script"]) {
				case 1: $stack .= "vertical-align: 50%;"; break;
				case 2: $stack .= "vertical-align: -50%;"; break;
			}
		}
		return $stack;
	} // end of function
// ------------------------------------------------------------------------------------------------


} // END OF CLASS
// ------------------------------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------

// WORKSHEET STRUCTURE
// ------------------------------------------------------------------------------------------------
class struct_sheet extends struct_book {

	var $name;
	var $biff_ver;
	var $options;
	var $unicode = false;
	var $num_rows;
	var $num_cols;
	var $_col_width = array();
	var $table = array();

	var $_parent;

	// ---------------------------------

// ------------------------------------------------------------------------------------------------
// CONSTRUCTOR
	function struct_sheet(&$book) {
		$this->_parent =& $book;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
// returns the XLS data in the form of PHP Array variable
	function get_array() {
		$fin = Array();
		$t_name = $this->name;
		if ($this->unicode) {
			$t_name = unicode2html($t_name);
		}

		$fin['SHEET_NAME'] = $t_name;
		$fin['SHEET_DATA'] = Array();

		for ($r=0;$r<=$this->num_rows;$r++) {
			for ($c=0;$c<=$this->num_cols;$c++) {
				$data = " "; $options = "";
				$cur_type = (isset($this->table[$r][$c]["type"]))?$this->table[$r][$c]["type"]:-2;
				if (isset($this->table[$r][$c]["data"]) || $cur_type == -1) {
					switch ($cur_type) {
						case -1: // blank
							$data = " ";
							break;
						case 0: // string
							$data = $this->_parent->_cell_data["data"][$this->table[$r][$c]["data"]];
							break;
						case 1; // int
							$data = $this->table[$r][$c]["data"];
							break;
						case 2; // double
							$data = $this->_check_format($this->table[$r][$c]["data"],$this->table[$r][$c]["xf"]);
							break;
						default:
							$data = $this->table[$r][$c]["data"];
							break;
					}
				}
				if (
					isset($this->table[$r][$c]["data"]) && 
					isset($this->_parent->_cell_data["unicode"][$this->table[$r][$c]["data"]]) && 
					$this->_parent->_cell_data["unicode"][$this->table[$r][$c]["data"]] && 
					!is_numeric($data)
					) {
					$data = unicode2html($data);
				}
				$fin['SHEET_DATA'][$r][$c] = $data;
			}
		}
		return $fin;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_html() {
		$merge_flag = 0;
		$do_not_draw = array();
		$fin = "<a name=\"".strtoupper(preg_replace("/[\s\.,\-]+/","_",$this->name))."\">";
		if ($this->_parent->draw_sheet_name) {
			$t_name = $this->name;
			if ($this->unicode) {
				$t_name = unicode2html($t_name);
			}

			$fin .= "<h4>".$t_name."</h4>\n";
		}
		$fin .= "<table ";
		$fin .= "border=".$this->_parent->border." ";
		$fin .= "cellpadding=".$this->_parent->cellpadding." ";
		$fin .= "cellspacing=".$this->_parent->cellspacing." ";
		$fin .= "class=\"xls_rd\" id=\"xls_rd\" bgcolor=\"".$this->_parent->bg_color."\">\n";
		if ($this->_parent->draw_index) {
			$fin .= "<tr>\n";
			$fin .= "<td class=idx>&nbsp;</td>";
			for ($c=0;$c<=$this->num_cols;$c++) {
				$fin .= "<td class=idx>&nbsp;";
				if ($c>25) {$fin .= chr((int)($c/26)+64);}
				$fin .= chr(($c%26)+65)."&nbsp;</td>";
			}
			$fin .= "</tr>\n";
		}
		for ($r=0;$r<=$this->num_rows;$r++) {
			$fin .= "<tr>\n";
			if ($this->_parent->draw_index) {
				$fin .= "<td class=idx>&nbsp;".($r+1)."&nbsp;</td>";
			}
			for ($c=0;$c<=$this->num_cols;$c++) {
				$data = "&nbsp;"; $options = "";
				$cur_type = (isset($this->table[$r][$c]["type"]))?$this->table[$r][$c]["type"]:false;
				if (isset($this->table[$r][$c]["data"]) || $cur_type == -1) {
					switch ($cur_type) {
						case -1: // blank
							$data = "&nbsp;";
							break;
						case 0: // string
							$data = $this->_parent->_cell_data["data"][$this->table[$r][$c]["data"]];
							break;
						case 1; // int
							if ($this->_parent->overr_int_align) {$options .= " class=xls_int";}
							$data = $this->table[$r][$c]["data"];
							break;
						case 2; // double
							if ($this->_parent->overr_double_align) {$options .= " class=xls_dbl";}
							$data = $this->_check_format($this->table[$r][$c]["data"],$this->table[$r][$c]["xf"]);
							break;
						default:
							$data = $this->table[$r][$c]["data"];
							break;
					}
				}
				if (!$this->_parent->overr_fnt_all && isset($this->table[$r][$c]["font"]) && $this->table[$r][$c]["font"] != 0) {
					$options .= " style=\"".$this->_parent->get_css_font($this->table[$r][$c]["font"]-1)."\"";
				}
				if (
					isset($this->_parent->_xf["fg_color"]) &&
					isset($this->table[$r][$c]["xf"]) &&
					isset($this->_parent->_palette[$this->_parent->_xf["fg_color"][$this->table[$r][$c]["xf"]]])
				) {
					$bg_color = $this->_parent->_palette[$this->_parent->_xf["fg_color"][$this->table[$r][$c]["xf"]]];
				}
				else {$bg_color = ""; }
				//if ($this->table[$r][$c]["type"] == -1) {
				//}
				if (strlen($bg_color)>2 && !$this->_parent->overr_bgcolor) {$options .= " bgcolor=#".$bg_color." ";}
				if (isset($this->table[$r][$c]["xf"])) {
					$h_al = (isset($this->_parent->_xf["h_align"][$this->table[$r][$c]["xf"]]))?$this->_parent->_xf["h_align"][$this->table[$r][$c]["xf"]]:0;
					$v_al = (isset($this->_parent->_xf["v_align"][$this->table[$r][$c]["xf"]]))?$this->_parent->_xf["v_align"][$this->table[$r][$c]["xf"]]:0;
				}
				else {
					$h_al = 0;
					$v_al = 0;
				}
				if (!$this->_parent->overr_align) {
					switch ($h_al) {
						case 0: break;
						case 1: $options .= " align=left"; break;
						case 2: $options .= " align=center"; break;
						case 3: $options .= " align=right"; break;
						case 4:
						case 5: $options .= " align=justify"; break;
					}
				}
				if (!$this->_parent->overr_valign) {
					switch ($v_al) {
						case 0: $options .= " valign=top"; break;
						case 1: $options .= " valign=middle"; break;
						case 2: $options .= " valign=bottom"; break;
					}
				}
				if (/* $r==0 && */ isset($this->_col_width[$c]) && !$this->_parent->overr_width) {
					$options .= " width=".round($this->_col_width[$c] * $this->_parent->mm2pix_rate);
				}
				if (
					isset($this->table[$r][$c]["data"]) && 
					isset($this->_parent->_cell_data["unicode"][$this->table[$r][$c]["data"]]) && 
					$this->_parent->_cell_data["unicode"][$this->table[$r][$c]["data"]] && 
					!is_numeric($data)
				) {
					$data = unicode2html($data);
				}
				$span = "";
				if (isset($this->_cells_merge_info) && isset($this->_cells_merge_info[$r][$c]) && sizeof($this->_cells_merge_info[$r][$c])>0) {
					
					//$data .= "mrg";
					$span = "";
					$merge_end_row = 0; $merge_end_col = 0;
					if (($this->_cells_merge_info[$r][$c]['last_row']-$r) > 0) {
						$span .= " rowspan=".($this->_cells_merge_info[$r][$c]['last_row']-$r+1)." ";
						$merge_end_row=$this->_cells_merge_info[$r][$c]['last_row'];
						$do_not_draw[$this->_cells_merge_info[$r][$c]['last_row']][$c] = true;
					}
					if (($this->_cells_merge_info[$r][$c]['last_col']-$c) > 0) {
						$span .= " colspan=".($this->_cells_merge_info[$r][$c]['last_col']-$c+1)." ";
						$merge_end_col = $this->_cells_merge_info[$r][$c]['last_col'];
						$merge_start_col = $c;
						$c+=($this->_cells_merge_info[$r][$c]['last_col']-$c); // ORIG
						$do_not_draw[$r][$this->_cells_merge_info[$r][$c]['last_col']] = true;
					}
					$fin .= "\t<td".$options." ".$span.">".nl2br($data)."</td>\n";
					// do_not_draw

					for ($sr=$r+1;$sr<=$merge_end_row;$sr++) {
						$do_not_draw[$sr][$c] = true;
						//for ($sc=1;$sc<=$merge_end_col;$sc++) {
						for ($sc=$merge_start_col;$sc<=$merge_end_col;$sc++) {
							$do_not_draw[$sr][$sc] = true;
						}
					}
					//--------------
				}
				else {
					if (!isset($do_not_draw[$r][$c]) || !$do_not_draw[$r][$c]) {
						$fin .= "\t<td".$options." $r $c >".nl2br($data)."</td>\n";
					}
				}
			}
			$fin .= "</tr>";
		}
		$fin .= "</table>\n";
		return $fin;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function get_raw() {
		$f_ar = array();
		for ($r=0;$r<=$this->num_rows;$r++) {
			for ($c=0;$c<=$this->num_cols;$c++) {
				$data = "";
				$cur_type = $this->table[$r][$c]["type"];
				$data = $this->_parent->_cell_data["data"][$this->table[$r][$c]["data"]];
				if ($this->_parent->_cell_data["unicode"][$this->table[$r][$c]["data"]]) {
					//$data = unicode2html($data);
				}
				$f_ar[$r][$c] = $data;
			}
		}
		return $f_ar;
	} // end of function
// ------------------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------------------
	function _check_format($num,$xf_idx) {
		$num_format_idx = $this->_parent->_xf["format"][$xf_idx];
		if ($num_format_idx > 0) {
			if (isset($this->_parent->_num_formats[$num_format_idx])) {
				$num_format = $this->_parent->_num_formats[$num_format_idx];
			}
			else {
				$num_format = $this->_parent->_bi_num_formats[$num_format_idx];
			}

			// CHECK DATE/TIME
			$num_format = preg_replace("/Red/i","RRR",$num_format);
			$format_tmp = preg_replace("/\[.*\]/","",$num_format);
			if (preg_match("/[m|d|y|h|s]|AM|PM/i",$format_tmp)) {
				return $this->_convert_date($num,$num_format);
			}
			else {
				return $this->_convert_number($num,$num_format);
			}
		}
		return $num;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _convert_number($num,$format) {
		if ($format == "") {return $num;}
		if (preg_match("/0\.0+/i",$format, $matches)) {
			$fract = strlen($matches[0]) - 2;
		}
		else {
			$fract = 0;
		}
		//preg_match("/0\.0+/i",$format, $matches);
		//$fract = strlen($matches[0]) - 2;
		if ($fract>0) {
			if (preg_match("/%/i",$format)) {$num = $num * 100;}
			$num = round($num,$fract);
			if (preg_match("/%/i",$format)) {$num .= "%";}
		}
		else if (preg_match("/0/i",$format)) {
			if (preg_match("/%/i",$format)) {$num = $num * 100;}
			$num = round($num);
			if (preg_match("/%/i",$format)) {$num .= "%";}
		}
		if (preg_match("/#[,\. ]##/",$format)) {
			$num = $this->_separate_1000($num);
		}
		if (preg_match("/\[\\$([^\-])\-/msi",$format, $matches)) {
			$curr = unicode2html($matches[1]);
			$num = $curr.$num;
		}
		return $num;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _separate_1000($i) {
		//$t_ar = explode(".",$i);
		$t_ar = preg_split("/[.,]/",$i);
		$i = $t_ar[0];
		$f = (isset($t_ar[1])) ? $t_ar[1] : "";
		if (preg_match("/\./",$i)) {
			$separator = ".";
		}
		else {$separator = ",";}
		$i = join($separator, split("\n\r", trim(strrev(chunk_split(strrev($i), 3)))));
		switch (strlen($f)) {
			case 1: $f .= "0"; break;
			case 2: break;
			default: $f .= "00"; break;
		}
		return $i.".".$f;
	} // end of function
// ------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------
	function _convert_date($date,$format) {
		// DATE PART
		if ($this->_parent->date_mode) {
			$date += 1462;
		}
		$days = (int) $date;
		$l = $days + 68569 + 2415019;
		$n = (int) ((4 * $l) / 146097);
		$l = $l - (int) ((146097 * $n + 3) / 4);
		$i = (int) ((4000 * ($l + 1)) / 1461001);
		$l = $l - (int) ((1461 * $i) / 4) + 31;
		$j = (int) ((80 * $l) / 2447);
		$date_ar['day'] = $l - (int) ((2447 * $j) / 80);
		if ($date_ar['day']<10) {$date_ar['day'] = "0".$date_ar['day'];}
		$l = (int) ($j / 11);
		$date_ar['month'] = $j + 2 - (12 * $l);
		if ($date_ar['month']<10) {$date_ar['month'] = "0".$date_ar['month'];}
		$date_ar['year'] = 100 * ( $n - 49 ) + $i + $l;
		// TIME PART
		$t_prst = $date - $days;
		$sec = round(86400 * $t_prst);
		$f_h = floor($sec / 3600);
		$f_m = floor(($sec - ($f_h * 3600)) / 60);
		$f_s = $sec - (($f_m * 60) + ($f_h * 3600));

		$date_ar['hours'] = $f_h;
		if ($date_ar['hours']<10) {$date_ar['hours'] = "0".$date_ar['hours'];}
		$date_ar['hours_12'] = ($f_h > 12) ? $f_h - 12 : $f_h;
		$date_ar['am_pm'] = ($f_h >= 12) ? "PM" : "AM";
		$date_ar['minutes'] = $f_m;
		if ($date_ar['minutes']<10) {$date_ar['minutes'] = "0".$date_ar['minutes'];}
		$date_ar['seconds'] = $f_s;
		if ($date_ar['seconds']<10) {$date_ar['seconds'] = "0".$date_ar['seconds'];}
		// APPLY FORMAT

		
		$format = preg_replace("/\[.*\]/","",$format);
		$format = preg_replace("/\\\/","",$format);
		$format = preg_replace("/\//",".",$format);
		$format = preg_replace("/[;@]/","",$format);
		$srch = array (
					"'a'i",
					"'p'i",
					"'m'i",
					"'y'i",
					"'d'i",
					"'h'i",
					"'s'i"
					);
		$rpls = array (
					"%a%",
					"%p%",
					"%m%",
					"%y%",
					"%d%",
					"%h%",
					"%s%"
					);
		$format = preg_replace ($srch, $rpls, $format);
		if (!preg_match("/%A%%M%|%P%%M%/i",$format)) {
			$format = preg_replace("/^%h%:/","%h%%h%:",$format);
		}
		else {
			$format = preg_replace("/^%h%%h%:/","%h%:",$format);
		}
		$srch = array (
					"'%A%%M%\.%P%%M%'i",
					"'\:%m%%m%'i",
					"'\:%m%'i",
					"'%m%%m%%m%%m%'i",
					"'%m%%m%%m%'i",
					"'%m%%m%'i",
					"'%m%'i",
					"'%y%%y%%y%%y%'i",
					"'%y%%y%'i",
					"'%d%%d%%d%%d%'i",
					"'%d%%d%'i",
					"'%d%'i",
					"'%h%%h%'i",
					"'%h%'i",
					"'%s%%s%'i",
					"'%s%'i"
					);
		$rpls = array (
					$date_ar['am_pm'],
					":".$date_ar['minutes'],
					":".(int)$date_ar['minutes'],
					$this->_parent->month_names[(int)$date_ar['month']-1],
					$this->_parent->month_names_short[(int)$date_ar['month']-1],
					$date_ar['month'],
					$date_ar['month'],
					$date_ar['year'],
					substr($date_ar['year'],-2),
					$date_ar['day'],
					$date_ar['day'],
					(int)$date_ar['day'],
					$date_ar['hours'],
					$date_ar['hours_12'],
					$date_ar['seconds'],
					(int)$date_ar['seconds']
					);
		$date = preg_replace ($srch, $rpls, $format);
		return $date;
	} // end of function
// ------------------------------------------------------------------------------------------------


} // END OF CLASS
// ------------------------------------------------------------------------------------------------


?>
