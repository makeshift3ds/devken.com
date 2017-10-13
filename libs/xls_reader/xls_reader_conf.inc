<?
//   CONFIGURATION FILE FOR XLS READER CLASS

// Default Table Settings
$border = 1;              // table border
$cellpadding = 2;         // cellpadding for the table
$cellspacing = 0;         // cellspacing for the table
$TableBgColor = "white";  // defauld background color

$Width2PixelsRate = 6.5;  // default mm->pixels conversion rate.
                          // exel has all the cell widths set im millimeters
                          // mm will be converted to pixels using this rate

$DrawIndex = true;        // if TRUe (1) - index sells will be drawn for columns and rows

$DrawSheetsList = true;   // if TRUE (1) - function get_workbook_html() will also create
                          // WorkSheets list proir to printing tables

$DrawSheetName = true;    // if TRUE (1) - sheet name will be drawn before every table
                          // sheetnames are drawn in <h4> ... </h4> - so you can tune the look of
                          // the Sheet names using CSS rules

//------------------------------------------------
// Using the setting below you will be able to disable
// formatting that is stored in XLS file

$OverrideIntAlign = false;    // if TRUE (1) - alignmet for INTEGERS that is stored in XLS file
                              // will be disabled. You will be able to control INTEGRES fromatting
                              // using .xls_int {...} CSS rule

$OverrideDoubleAlign = false; // if TRUE (1) - alignmet for DOUBLES that is stored in XLS file
                              // will be disabled. You will be able to control DOUBLES fromatting
                              // using .xls_dbl {...} CSS rule

$OverrideAlign = false;       // if TRUE (1) - horizontal alignment that is stored in XLS file
                              // will be ignored

$OverrideVAlign = false;      // if TRUE (1) - vertical alignment that is stored in XLS file
                              // will be ignored

$OverrideBgColor = false;     // if TRUE (1) - background cell color that is stored in XLS file
                              // will be ignored

$OverrideWidth = true;       // if TRUE (1) - cell width that is stored in XLS file
                              // will be ignored

//------------------------------------------------
// Using the setting below you will be able to ignore
// font settings that are stored in XLS file

$OverrideFontAll = false;        // if TRUE (1) - ignores all font settings stored in XLS
$OverrideFontColor = false;      // if TRUE (1) - ignores font color stored in XLS
$OverrideFontBold = false;       // if TRUE (1) - ignores font weight stored in XLS
$OverrideFontSize = false;       // if TRUE (1) - ignores font size stored in XLS
$OverrideFontName = false;       // if TRUE (1) - ignores font name stored in XLS
$OverrideFontItallic = false;    // if TRUE (1) - ignores itallic font setting stored in XLS
$OverrideFontUnderline = false;  // if TRUE (1) - ignores underline font setting stored in XLS
$OverrideFontScript = true;      // if TRUE (1) - ignores script font setting stored in XLS


//------------------------------------------------
// In this section you can provide localy names for months
// they will be used to represent dates
// if ommited - English names will be used.
/*
$MonthsNames = Array (
   "January", "February",
   "March", "April", "May",
   "June", "July", "August",
   "September", "October", "November",
   "December"
);
$MonthsNamesShort = Array (
   "Jan", "Feb",
   "Mar", "Apr", "May",
   "Jun", "Jul", "Aug",
   "Sep", "Oct", "Nov",
   "Dec"
);
*/

?>
