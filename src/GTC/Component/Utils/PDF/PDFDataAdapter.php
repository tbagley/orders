<?php

/** 
 * A PDF data formatter
 * 
 * @todo Create a generic/raw formatter
 */
namespace GTC\Component\Utils\PDF;

class PDFDataAdapter
{
    static $keywords = array('title', 'columns');
    
    private static function isKeyword($key)
    {
        return in_array($key, PDFDataAdapter::$keywords);
    }
    
    /**
     * Univeral format method that try to determine how to format the raw data
     * 
     * @param type $data
     * @param type $orientation
     * @return type
     * @throws Exception
     */
    public static function format($data, $orientation = '')
    {
        if(isset($data['sEcho'])) {
            return PDFDataAdapter::formatDataTable($data, $orientation);
        } elseif(isset($data['report'])) {
            return PDFDataAdapter::formatReport($data, $orientation);
        } else {
            throw new Exception('Unrecognized/malformed data structure');
            return false;
        }
    }

    /**
     * Reads a report data structure and rebuild a structure readable by TCPDFBuilder
     * 
     * @param type $report_output Multidimensional array
     * @param type $orientation Defaults to landscape
     * @return string
     */
    public static function formatReport($report_output, $orientation = '')
    {
        if($orientation != '') {
            $data = array(array('setOrientation', array($orientation)));
        }

        if(!empty($report_output['report']['title'])) {
            $data[] = array('createTitle', array($report_output['report']['title']));
        }
        $section = array();

        // Column 1 of the section
        $temp = array('label' => 'Report Type', 'value' => (!empty($report_output['report']['report_type']) ? $report_output['report']['report_type'] : 'Unknown'));
        $section[0] = !empty($section[0]) ? array_merge($section[0], array('report_type' => $temp)) : array('report_type' => $temp);

        if(!empty($report_output['report']['time_generated'])) {
            $temp = array('label' => 'Generated', 'value' => $report_output['report']['time_generated']);
            $section[0] = !empty($section[0]) ? array_merge($section[0], array('time_generated' => $temp)) : array('time_generated' => $temp);
            
        }
        if(!empty($report_output['report']['criteria'])) {
            $section[0] = !empty($section[0]) ? array_merge($section[0], $report_output['report']['criteria']) : $report_output['report']['criteria'];
        }
        
        // Column 2 of the section
        if(!empty($report_output['report']['summary'])) {
            $section[1] = !empty($section[1]) ? array_merge($section[1], $report_output['report']['summary']) : $report_output['report']['summary'];
        }
        
        $data[] = array('createSection', array($section));
        
        // Table of the data set
        $columns = !empty($report_output['report']['columns']) ? $report_output['report']['columns'] : array();
        if(!empty($report_output['report_data'])) {
            $data[] = array('createTable', array($report_output['report_data'], $columns));
        } elseif(!empty($report_output['units'])) {
            foreach($report_output['units'] as $key => $unit) {
                if(!empty($unit['report_data'])) {
                    $title = !empty($unit['report_title']) ? $unit['report_title'] : '';
                    $data[] = array('createTable', array($unit['report_data'], $columns, $title));
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Get a datatable data structure and rebuild a structure readable by TCPDFBuilder
     * 
     * @param type $datatable_output
     * @param type $orientation
     */
    public static function formatDataTable($datatable_output, $orientation = '')
    {
        if($orientation != '') {
            $data = array(array('setOrientation', array($orientation)));
        }
        $data[] = array('createTitle', array($datatable_output['title']));
        $columns = !empty($datatable_output['columns']) ? $datatable_output['columns'] : array();
        if(!empty($datatable_output['data'])) {
            $data[] = array('createTable', array($datatable_output['data'], $columns));
        }
    }
}