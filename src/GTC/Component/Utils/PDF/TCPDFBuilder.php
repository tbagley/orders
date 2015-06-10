<?php

/** 
 * A TCPDF wrapper/interface class
 * 
 */
namespace GTC\Component\Utils\PDF;

use TCPDF;

class TCPDFBuilder extends \TCPDF
{  
    // page settings
    private $content_dimension;

    // table settings
    // "tableheader" settings
    private $th_bg_rgb          = array();
    private $th_border_rgb      = array();
    private $th_border_size;
    private $th_font_rgb        = array();
    private $th_font            = array();
    
    // "tablerow" settings
    private $tr_bg_rgb          = array();
    private $tr_border_rgb      = array();
    private $tr_border_size;
    private $tr_font_rgb        = array();
    private $tr_font            = array();
    
    // "section" settings
    private $sec_bg_rgb          = array();
    private $sec_border_rgb      = array();
    private $sec_border_size;
    private $sec_font_rgb        = array();
    private $sec_font            = array();
    
    // "title" settings
    private $title_bg_rgb          = array();
    private $title_border_rgb      = array();
    private $title_border_size;
    private $title_font_rgb        = array();
    private $title_font            = array();
    
    // "default" font/border settings
    private $bg_rgb          = array();
    private $border_rgb      = array();
    private $border_size;
    private $font_rgb        = array();
    private $font            = array();
    
    // defaults setting
    private $max_cell_width         = 100;
    /**
     * When data count exceed the set threshold the PDF generator will switch over to 
     * estimating line count per table row (will boost performance but table lines may be off) 
     * 
     * @var int $data_threshold Set the threshold of when to use line count estimation 
     * counting per row for creating table
     */
    private $data_threshold         = 100;
    
    // data/table info
    private $table_columns          = array();
    private $table_column_width     = array();
    private $data_count;
    
    /**
     * Pass the parameters to the parent constructor TCPDF; intialize default settings and auto add a page
     * 
     * Refer to TCPDF notes on constructor
     * 
     * @param type $orientation
     * @param type $unit
     * @param type $format
     * @param type $unicode
     * @param type $encoding
     * @param type $diskcache
     */
    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);

        $this->initializeDefaultSettings();
        $this->loadPageSettings();
        $this->loadGroupSettings('default');

        // remove header and footer since it's not used (to remove the auto generated header line)
        $this->SetPrintHeader(false);   
        $this->SetPrintFooter(false);
        $this->AddPage();
    }

    /**
     *  set document information
     * 
     * @param type $title
     * @param type $subject
     * @param type $author
     * @param type $keywords
     */
    public function setInformation($title = '', $subject = '', $author = '', $keywords = '')
    {
        $this->SetCreator(PDF_CREATOR);
        
        if($title != '')
            $this->SetTitle($title);
        if($subject != '')
            $this->SetSubject($subject);
        if($author != '')
            $this->SetAuthor($author);
        if($keywords != '')
            $this->SetKeywords($keywords);
    }
    
    /**
     * Initialize, or reinitialize, the default settings for all font and border settings
     * 
     * Note: if any new group is added the PDF::getGroupProperty() method needs to accomodate for the new group
     */
    public function initializeDefaultSettings()
    {
        //table header settings
        $this->th_bg_rgb        = array('r' => 222, 'g' => 222, 'b' => 222);
        $this->th_border_rgb    = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->th_border_size   = 0.2;
        $this->th_font_rgb      = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->th_font          = array('family' =>'helvetica', 'style' => 'B', 'size' => '10', 'fontfile' => '');
        
        //table row settings
        $this->tr_bg_rgb        = array('r' => 240, 'g' => 240, 'b' => 240);
        $this->tr_border_rgb    = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->tr_border_size   = 0.2;
        $this->tr_font_rgb      = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->tr_font          = array('family' =>'helvetica', 'style' => '', 'size' => '8', 'fontfile' => '');
        
        //section settings
        $this->sec_bg_rgb        = array('r' => 240, 'g' => 240, 'b' => 240);
        $this->sec_border_rgb    = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->sec_border_size   = 0.2;
        $this->sec_font_rgb      = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->sec_font          = array('family' =>'helvetica', 'style' => '', 'size' => '10', 'fontfile' => '');
        
        //default font/border
        $this->bg_rgb        = array('r' => 240, 'g' => 240, 'b' => 240);
        $this->border_rgb    = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->border_size   = 0.2;
        $this->font_rgb      = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->font          = array('family' =>'helvetica', 'style' => '', 'size' => '8', 'fontfile' => '');
        
        //title settings
        $this->title_bg_rgb        = array('r' => 240, 'g' => 240, 'b' => 240);
        $this->title_border_rgb    = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->title_border_size   = 0.2;
        $this->title_font_rgb      = array('r' => 0, 'g' => 0, 'b' => 0);
        $this->title_font          = array('family' =>'helvetica', 'style' => 'B', 'size' => '14', 'fontfile' => '');    
    }
    
    /**
     * Helper function to determine the group/type of properties
     * 
     * @param string $group Specify group; available options: 'tableheader', 'tablerow', 'section', 'title', 'default'
     * @param string $property Specify which property (for the specified type)
     * @return boolean|string
     */
    private function getGroupProperty($group, $property)
    {
        $group = strtolower(trim($group));

        switch ($group) {
            case 'tableheader':
                $property = 'th'.$property;
                break;
            case 'tablerow':
                $property = 'tr'.$property;
                break;
            case 'section':
                $property = 'sec'.$property;
                break;
            case 'title':
                $property = 'title'.$property;
                break;
            case 'default':
                $property = ltrim($property, '_');
                break;
        }
        
        if(property_exists(__CLASS__, $property)) {
            return $property;
        } else {
            return false;
        }
    }
    
    /**
     * Allows each group's border settings to be changed
     * 
     * @param string $group Specify group; available options: 'tableheader', 'tablerow', 'section', 'title', 'default'
     * @param array|string $background_color Set background color; expects an array containing the values for rgb or an hex/html color code 
     * @param float|int $border_size The size of the border (unknown unit; use decimal value 0.1 - 0.9 should be sufficient for typical use)
     * @param array|string $border_color Set border color; expects an array containing the values for rgb or an hex/html color code 
     */
    public function setGroupBorder($group, $background_color = '', $border_size = '', $border_color = '')
    {
        $bg_rgb = $this->getGroupProperty($group, '_bg_rgb');
        $bd_rgb = $this->getGroupProperty($group, '_border_rgb');
        $bd_size = $this->getGroupProperty($group, '_border_size');
        
        if($background_color != '' AND $bg_rgb !== false) {
            if(is_array($background_color) AND isset($background_color['r']) AND isset($background_color['g']) AND isset($background_color['b'])) {
                $this->$bg_rgb = $background_color;
            } elseif(is_string($background_color)) {
                //assumes hex color
                $this->$bg_rgb = array_change_key_case($this->convertHTMLColorToDec($background_color), CASE_LOWER);
            } 
        }
        
        if($border_color != '' AND $bd_rgb !== false)
        {
            if(is_array($border_color) AND isset($border_color['r']) AND isset($border_color['g']) AND isset($border_color['b'])) {
                $this->$bd_rgb = $border_color;
            } elseif(is_string($border_color)) {
                //assumes hex color
                $this->$bd_rgb = array_change_key_case($this->convertHTMLColorToDec($border_color), CASE_LOWER);
            }           
        }
        
        if(is_numeric($border_size) AND $bd_size !== false) {
            $this->$bd_size = $border_size;
        }
    }
    
    /**
     * Allows each group's font settings to be changed
     * 
     * @param string $group Specify group; available options: 'tableheader', 'tablerow', 'section', 'title', 'default',
     * @param int $size Refer to TCPDF documentation on TCPDF::SetFont()
     * @param array|string $color Set text color; expects an array containing the values for rgb or an hex/html color code 
     * @param string $style Refer to TCPDF documentation on TCPDF::SetFont()
     * @param string $family Refer to TCPDF documentation on TCPDF::SetFont()
     * @param string $fontfile Refer to TCPDF documentation on TCPDF::SetFont()
     */
    public function setGroupFont($group, $size = '', $color = '', $style = '', $family = '', $fontfile = '')
    {
        $font = $this->getGroupProperty($group, '_font');
        $font_rgb = $this->getGroupProperty($group, '_font_rgb');
        
        if(is_numeric($size) AND $font !== false) {
            $this->{$font}['size'] = $size;
        }
        
        if($style != '' AND $font !== false) {
            $this->{$font}['style'] = $style;
        }
        
        if($family != '' AND $font !== false) {
            $this->{$font}['family'] = $family;
        }
        
        if($fontfile != '' AND $font !== false) {
            $this->{$font}['fontfile'] = $fontfile;
        }
        
        if($color != '' and $font_rgb !== false) {
            if(is_array($color) AND isset($color['r']) AND isset($color['g']) AND isset($color['b'])) {
                $this->$font_rgb = $color;
            } elseif(is_string($color)) {
                //assumes hex color
                $this->$font_rgb = array_change_key_case($this->convertHTMLColorToDec($color), CASE_LOWER);
            }
        }
    }
    
    /**
     * Load a group font and border settings
     * 
     * @param string $group Specify group; available options: 'tableheader', 'tablerow', 'section', 'title', 'default'
     */
    public function loadGroupSettings($group)
    {
        $font = $this->getGroupProperty($group, '_font');
        $font_rgb = $this->getGroupProperty($group, '_font_rgb');
        $bg_rgb = $this->getGroupProperty($group, '_bg_rgb');
        $bd_rgb = $this->getGroupProperty($group, '_border_rgb');
        $bd_size = $this->getGroupProperty($group, '_border_size');
        
        if($font !== false) {
            $this->SetFont($this->{$font}['family'], $this->{$font}['style'], $this->{$font}['size'], $this->{$font}['fontfile']);
        }
        if($font_rgb !== false) {
            $this->SetTextColor($this->{$font_rgb}['r'], $this->{$font_rgb}['g'], $this->{$font_rgb}['b']);
        }
        if($bg_rgb !== false) {
            $this->SetFillColor($this->{$bg_rgb}['r'], $this->{$bg_rgb}['g'], $this->{$bg_rgb}['b']);
        }
        if($bd_rgb !== false) {
            $this->SetDrawColor($this->{$bd_rgb}['r'], $this->{$bd_rgb}['g'], $this->{$bd_rgb}['b']);
        }
        if($bd_size !== false) {
            $this->SetLineWidth($this->$bd_size);
        }
    }
    
    /**
     * Caculate the height of a row
     * 
     * @param type $data
     * @param array $width an array of the keys from $data assigned with a width length
     * @param boolean $exact Returns the actual height used (due to transaction usage there 
     * is a performance overhead if turned on for a large data set; roughly double the time
     * it takes to generate the pdf if turned on)
     * @return type
     */
    public function getRowHeight($data, $width, $exact = false)
    {
        return $this->getRowNumLines($data, $width, $exact)*6;
    }
    
    /**
     * Caculate the number of lines needed for a table row (defaults to quick estimation)
     * 
     * @param array $data
     * @param array $width an array of the keys from $data assigned with a width length
     * @param boolean $exact Returns the actual lines used (due to transaction usage there 
     * is a performance overhead if turned on for a large data set; roughly double the time
     * it takes to generate the pdf if turned on) 
     */
    private function getRowNumLines($data, $width, $exact = false)
    {
        $lines = array(0);
        $total_line = 1;

        if($exact) {    // get exacts lines used by row
            // store current object
            $this->startTransaction();

            foreach($width as $k => $w) {
                // get the number of lines for multicell
                if(!empty($data[$k])) {
                    $lines[] = $this->MultiCell($w, 0, $data[$k], 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                }
            }

            // restore previous object
            $this->rollbackTransaction(true);
            
            $total_line = max($lines);
        } else {    // get an estimated number of lines used by row
            foreach($width as $k => $w) {
                if(!empty($data[$k])) {
                    $lines[] = $this->getNumLines($data[$k], $w);
                }
            }
            /**
             * Note: adding an additional line since getNumLines() is an estimation only and 
             * therefore is sometime off but this will sometime provide more lines then needed 
             */
            $total_line = max($lines) + 1;
        }
        
        return $total_line;
    }
    
    /**
     * Load some page setting
     */
    private function loadPageSettings()
    {   
        // what unit measurement is 'k' in? is this dependant on the unit passed to the constructor
        $dim = $this->getPageDimensions();
        $l_margin = $dim['wk'] * 0.05;
        $r_margin = $dim['wk'] * 0.05;
        $t_margin = $dim['hk'] * 0.05;
        $b_margin = $dim['hk'] * 0.05;
        
        $this->content_dimension['wk'] = floor($dim['wk'] - ($l_margin + $r_margin));
        $this->content_dimension['hk'] = floor($dim['hk'] - ($t_margin + $b_margin));
        
        $this->SetMargins($l_margin, $t_margin, $r_margin);
        
        //$this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(TRUE, $b_margin);
        
        // need to reset current x position (should keep the y position)
        $this->x = $this->lMargin;
    }
    
    /**
     * Setup the table columns and calculate each column width and 
     * 
     * @param type $data
     * @todo Possibly align the stacked columns if the columns are stacked on each other on multiple lines
     */
    private function setupTableColumns(&$data, $columns = array())
    {
        if(empty($columns)) {
            $columns = array();
            $header = array_flip(array_keys(current($data)));
            foreach($header as $key => $label) {
                //attempt to create a "readable" label to display for the headers
                $columns[$key] = ucwords(preg_replace('/[^a-zA-Z0-9#$%]/', ' ', $key));
            }
        } else {
            $header = $columns;
        }

        $this->loadGroupSettings('tableheader');

        //calculate each column width
        foreach($header as $key => $value) {
            $newlen = $this->GetStringWidth($columns[$key]) + 2;
            $header[$key] = 0;
            $header[$key] = ceil(($newlen > $this->max_cell_width) ? $this->max_cell_width : $newlen);   
        }
        
        //load settings so that width/height are calculated correctly
        $this->loadGroupSettings('tablerow'); 

        $counter = 0;
        foreach($data as $row) {
            foreach($header as $key => $len) {
                if(isset($row[$key]) AND $header[$key] < $this->max_cell_width) {
                    $newlen = $this->GetStringWidth($row[$key]) + 2;
                    $header[$key] = ceil(($newlen > $len) ? (($newlen > $this->max_cell_width) ? $this->max_cell_width : $newlen) : $len);                      
                }
            }
            // don't want to iterate through the whole data set; just want a small set 
            // to calculate the columns width; the 100th iteration to break on is arbitrary 
            if($counter >= 100) {
                break;
            }
            $counter++;
        }
        
        // checks if any columns need to be wrapped to a new line
        $this->table_column_width = $header;
        $length = 0;
        $line = 0;
        $prev = '';
        $this->table_columns = array();
        foreach($this->table_column_width as $key => $width) {
            $length += $width;
            $this->table_columns[$line] = isset($this->table_columns[$line]) ? $this->table_columns[$line] : array();
            
            if($length <= $this->content_dimension['wk']) {
                $this->table_columns[$line][$key] = $columns[$key];
            } elseif($length > $this->content_dimension['wk']) {
                // extend the last column of this line to fit the table
                $diff = $this->content_dimension['wk'] - ($length - $width);
                $this->table_column_width[$prev] += $diff;
                
                $length = $width;
                
                //make new line
                $line++;
                $this->table_columns[$line][$key] = $columns[$key];
            }
            $prev = $key;
        }

        // extend the last column to fit the table
        if($length < $this->content_dimension['wk']) {
            $this->table_column_width[$prev] += ($this->content_dimension['wk'] - $length);
        }
    }
    
    /**
     * Set the orientation and recalculate page settings
     * 
     * @param string $orientation Define the orientation of the page
     */
    public function setOrientation($orientation = '')
    {
        if($this->CurOrientation != $orientation) {
            $this->setPageOrientation($orientation);
            //recalculate page settings
            $this->loadPageSettings();
        }
    }
    
    /**
     * Create the document title
     * 
     * @param string $title
     * @param boolean $background If true then display background color
     * @param boolean $border If true then display border around the title
     */
    public function createTitle($title, $background = false, $border = false)
    {
        $this->loadGroupSettings('title');
        $bg = ($background) ? 1 : 0;
        $bd = ($border) ? 1 : 0;
        $this->MultiCell('', '', $title, $bd, 'L', $bg);
        $this->Ln();
    }
    
    /**
     * Create a text box
     * 
     * @param string $text
     * @param boolean $backgroud If true then display background color
     * @param boolean $border If true then display border around the title
     */
    public function createText($text, $backgroud = false, $border = false)
    {
        $this->loadGroupSettings('default');
        $bg = ($background) ? 1 : 0;
        $bd = ($border) ? 1 : 0;
        $this->MultiCell('', '', $text, $bd, 'L', $bg);
        $this->Ln();
    }

    /**
     * Create a section of equally sized columns of text with outer border
     * 
     * @param array $data multi dimension array containing arrays (the columns) of arrays (the text in the column) of labels and values;
     * @param boolean $background If true display the background color (defaults to true)
     * @param boolean $border if true display the border (defaults to true)
     * @todo Possibly use cell per text or use html table; 
     *       possibly dumb down the specific data structure it's expecting
     */
    public function createSection($data, $background = true, $border = true)
    {
        $this->loadGroupSettings('section');
        $w = $this->content_dimension['wk']/count($data);
        $bg = $background   ? 1 : 0;
        $bd_1 = $border     ? 1 : 0;
        $bd_tlb = $border   ? 'TLB' : 0;
        $bd_trb = $border   ? 'TRB' : 0;
        $bd_tb = $border    ? 'TB' : 0;
        
        $temp = array();
        foreach($data as $key => $col) {
            
            $text = '';
            foreach($col as $l => $row) {
                if(!empty($row['label']) AND !empty($row['value'])) {
                    $text .= $row['label'].": ".$row['value']."\n";
                } elseif(is_array($row)) {
                    reset($row);
                    $text .= current($row).": ".next($row)."\n";
                } else {
                    $text .= $row."\n";
                }
            }
            $temp[] = trim($text);
            $width[] = $w;
        }
        
        reset($temp);
        $firstkey = key($temp);
        end($temp);
        $lastkey = key($temp);
        $h = $this->getRowHeight($temp, $width, true);
        
        foreach($temp as $key => $text) {
            if(count($data) == 1) {
                $this->MultiCell($w, $h, $text, $bd_1, 'L', $bg, 0);
            } elseif($key == $firstkey) {
                $this->MultiCell($w, $h, $text, $bd_tlb, 'L', $bg, 0);
            } elseif($key == $lastkey) {
                $this->MultiCell($w, $h, $text, $bd_trb, 'L', $bg, 0);
            } else {
                $this->MultiCell($w, $h, $text, $bd_tb, 'L', $bg, 0);
            }
        }
        $this->Ln();
        // Creating another new line (the spacing seems off when using consecutive TCPDF::Ln())
        $this->Write(0, '');
        $this->Ln();
    }
    
    /**
     * Generate a table using the provided data
     * 
     * @param type $data An array of assosicated arrays
     * @param array $columns The $columns keys must match the keys in $data and the value will be the display label
     */
    public function createTable($data, $columns = array(), $title = '')
    {
        // if the data is not much then do exact line count (per row)
        // otherwise do an estimation line count to boost performance
        $this->data_count = count($data);
        $exact = ($this->data_count <= $this->data_threshold) ? true : false;

        // Write out the table title
        if($title != '') {
            $this->loadGroupSettings('tableheader');
            $this->SetFont($this->th_font['family'], 'B', $this->th_font['size']);
            $this->Write('', $title); 
            $this->Ln();
        }
        
        // Write the table headers/columns
        $this->setupTableColumns($data, $columns);
        $this->loadGroupSettings('tableheader');
        foreach($this->table_columns as $l => $cols) {
            $h = $this->getRowHeight($cols, $this->table_column_width, true);
            foreach($cols as $key => $label) {
                $this->MultiCell($this->table_column_width[$key], $h, $label, 1, 'C', 1, 0);
            }
            $this->Ln();
        }
        
        // Write the table rows
        $this->loadGroupSettings('tablerow');
        $fill = 0; 
        foreach($data as $row) {
            
            foreach($this->table_columns as $l => $cols) {
                $temprow = array();
                foreach($cols as $key => $label) {
                    $temprow[$key] = isset($row[$key]) ? $row[$key] : '';
                }
                $h = $this->getRowHeight($temprow, $this->table_column_width, $exact);
                foreach($temprow as $key => $val) {
                    switch($key){
                        case 'verified' : switch ($val) {
                                            case     1  :
                                            case    '1' : $val = 'Verified';
                                                          break;
                                                default : $val = 'Pending'; 
                                          }
                                          break;
                    }
                    $this->MultiCell($this->table_column_width[$key], $h, $val , 1, 'L', $fill, 0);
                }
                $this->Ln();
            }
            $fill=!$fill;
        }
        $this->Ln();
    }
    
    /**
     * Create the pdf document after the data structure is normalize
     */
    public function create($data)
    {
        foreach($data as $row) {
            $method = !empty($row[0]) ? $row[0] : false;
            $arguments = !empty($row[1]) ? $row[1] : array();
            
            if($method === false) {
                continue;
            }
            
            if(method_exists(__CLASS__, $method)) {
                call_user_func_array(array($this, $method), $arguments);
            } else {
                throw new Exception(get_class($this->pdf).'::'.$method.'() does not exist');
            }
        }
    }
}