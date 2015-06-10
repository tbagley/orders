<?php
/**
 * Created by PhpStorm.
 * User: mrecho
 * Date: 1/13/14
 * Time: 4:57 PM
 */

namespace GTC\Component\Utils;

use GTC\Component\Utils\Arrayhelper;


class DataTableHelper {

    /**
     * Used to sort the DataTables ajax Sort params
     *
     * @param $params
     * @param $unsorteddata
     * @return array
     */
    public function sortDataTables($params,$unsorteddata)
    {

        $aColumns                       = array();        // datatable columns event field/key names
        $searchfields                   = array();

        for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
            $aColumns[] = $params['mDataProp_'.$i];

            if ($params['bSearchable_'.$i] == "true") {
                $searchfields[] = $params['mDataProp_'.$i];
            }
        }

        // if doing a column sorting
        if ( isset($params['iSortCol_0']) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
            $sorteddata = DataTableHelper::filterQuickHistorySort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $unsorteddata);
        } else {
            $sorteddata = $unsorteddata;
        }
        unset($unsorteddata);


        return $sorteddata;
    }//sortDataTables


    /**
     * Return vehicles events having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array $unit_events
     *
     * @return array $results
     */
    public function filterQuickHistorySort($column_name, $sort_order, $unit_events)
    {
        $results = $unit_events;
        $sorting_order = '<';       // ascending sort by default
        if ( $sort_order == 'desc') {
            $sorting_order = '>';       // descending sort
        }

        if ( isset($column_name) AND $column_name != "" ) {
            switch($sorting_order) {
                case '<':
                    usort($results, Arrayhelper::usort_compare_asc($column_name));
                    break;
                case '>':
                    usort($results, Arrayhelper::usort_compare_desc($column_name));
                    break;
            }
        }

        return $results;

    }

} 