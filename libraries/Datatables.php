<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Ignited Datatables
 *
 * This is a wrapper class/library based on the native Datatables server-side implementation by Allan Jardine
 * found at http://datatables.net/examples/data_sources/server_side.html for CodeIgniter
 *
 * @package CodeIgniter
 * @subpackage libraries
 * @category library
 * @version 0.6
 * @author Vincent Bambico <metal.conspiracy@gmail.com>
 * Yusuf Ozdemir <yusuf@ozdemir.be>
 * @link http://codeigniter.com/forums/viewthread/160896/
 * @link https://github.com/IgnitedDatatables/Ignited-Datatables/wiki
 */
 
 /**
  * change log:
  * tambahan rownum 20140912
  # ex: $this->datatables->select('{rownum} as rownum')
  
 */
class Datatables
{
    /**
     * Global container variables for chained argument results
     *
     */
    protected $ci;
    protected $table;
    protected $select = array();
    protected $joins = array();
    protected $columns = array();
    protected $where = array();
    protected $filter = array();
    protected $add_columns = array();
    protected $edit_columns = array();
    protected $unset_columns = array();
    protected $date_columns = array();
    protected $rupiah_columns = array();
	protected $numeric_columns = array();
	protected $checkbox_columns = array();

    //edited by miftah//
    protected $order_bys = array();
	
	//add by irul
    protected $group_bys = array();
	

    /**
     * Copies an instance of CI
     */
    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * Generates the SELECT portion of the query
     *
     * @param string $columns
     * @param bool $backtick_protect
     * @return mixed
     */
    public function select($columns, $backtick_protect = TRUE)
    {
        foreach ($this->explode(',', $columns) as $val) {
            $column                = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
            $this->columns[]       = $column;
            
            //irul - tambahan rownum 20140912
            $mystring = $val;
            $findme   = '{rownum}';
            $pos = strpos($mystring, $findme);
            if ($pos === true) {
                $rownum_order =  $this->get_rownum();
                $val = str_replace($findme, $rownum_order, $val);
                $this->select[$column] = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$1', $val));
            } else
                $this->select[$column] = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$1', $val));
        }
        
        //irul - tambahan rownum 20140912
        $rownum_order =  $this->get_rownum();
        $columns = str_replace($findme, $rownum_order, $columns);
        
        $this->ci->db->select($columns, $backtick_protect);
        return $this;
    }
    
    //irul - tambahan rownum 20140912
    protected function get_rownum()
    {
        if ($this->check_mDataprop())
            $mColArray = $this->get_mDataprop();
        elseif ($this->ci->input->get('sColumns'))
            $mColArray = explode(',', $this->ci->input->get('sColumns'));
        else
            $mColArray = $this->columns;

        $mColArray = array_values(array_diff($mColArray, $this->unset_columns));
        $columns   = array_values(array_diff($this->columns, $this->unset_columns));

        $rownum_order = '';
        for ($i = 0; $i < intval($this->ci->input->get('iSortingCols')); $i++)
            if (isset($mColArray[intval($this->ci->input->get('iSortCol_' . $i))]) 
            && in_array($mColArray[intval($this->ci->input->get('iSortCol_' . $i))], $columns) 
            && $this->ci->input->get('bSortable_' . intval($this->ci->input->get('iSortCol_' . $i))) == 'true')
                $rownum_order .= $this->select[$mColArray[intval($this->ci->input->get('iSortCol_' . $i))]].' '.$this->ci->input->get('sSortDir_' . $i).',';
            
        $rownum_order = substr($rownum_order , 0, -1);
        // $rownum = empty($rownum_order) ? '0' : '(row_number() over (order by '.$rownum_order.'))';
        
        if(empty($rownum_order)) {
            //order manual. dt->order_bys(); cuma mesti taro diatas select; biar kebaca :D
            $order_cols = $this->order_bys;
            if(count($order_cols) > 0) {
                for ($i = 0; $i < count($order_cols); $i++) 
                    $rownum_order .= $order_cols[$i][0].' '.$order_cols[$i][1].',';
                $rownum_order = substr($rownum_order , 0, -1);
                $rownum = empty($rownum_order) ? '0' : '(row_number() over (order by '.$rownum_order.'))';
            } else $rownum = '0';
        } else
            $rownum = '(row_number() over (order by '.$rownum_order.'))';
            
            
        return $rownum;
    }

    /**
     * Generates the FROM portion of the query
     *
     * @param string $table
     * @return mixed
     */
    public function from($table)
    {
        $this->table = $table;
        $this->ci->db->from($table);
        return $this;
    }


    /**
     * Generates the JOIN portion of the query
     *
     * @param string $table
     * @param string $fk
     * @param string $type
     * @return mixed
     */
    public function join($table, $fk, $type = NULL)
    {
        $this->joins[] = array(
            $table,
            $fk,
            $type
        );
        $this->ci->db->join($table, $fk, $type);
        return $this;
    }

    public function order_by($x, $y)
    {
        $this->order_bys[] = array(
            $x,
            $y
        );
        $this->ci->db->order_by($x, $y);
        return $this;
    }
	
	
	//add by irul
    public function group_by($columns)
    {
		$this->ci->db->group_by($columns);		
        return $this;
    }

    /**
     * Generates the WHERE portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function where($key_condition, $val = NULL, $backtick_protect = TRUE)
    {
        $this->where[] = array(
            $key_condition,
            $val,
            $backtick_protect
        );
        $this->ci->db->where($key_condition, $val, $backtick_protect);
        return $this;
    }

    /**
     * Generates the WHERE portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function filter($key_condition, $val = NULL, $backtick_protect = TRUE)
    {
        $this->filter[] = array(
            $key_condition,
            $val,
            $backtick_protect
        );
        return $this;
    }

    /**
     * Sets additional column variables for adding custom columns
     *
     * @param string $column
     * @param string $content
     * @param string $match_replacement
     * @return mixed
     */
    public function add_column($column, $content, $match_replacement = NULL)
    {
        $this->add_columns[$column] = array(
            'content' => $content,
            'replacement' => $this->explode(',', $match_replacement)
        );
        return $this;
    }

    /**
     * Sets additional column variables for editing columns
     *
     * @param string $column
     * @param string $content
     * @param string $match_replacement
     * @return mixed
     */
    public function edit_column($column, $content, $match_replacement)
    {
        $this->edit_columns[$column][] = array(
            'content' => $content,
            'replacement' => $this->explode(',', $match_replacement)
        );
        return $this;
    }


    //irul
    /*
	public function rupiah_column($column) {
		$this->rupiah_columns[] = $column;
        return $this;
    }
	*/

    public function rupiah_column($columns)
    {
        foreach ($this->explode(',', $columns) as $val) {
            $column                 = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
            $this->rupiah_columns[] = $column;
        }
        return $this;
    }

    public function date_column($columns)
    {
        foreach ($this->explode(',', $columns) as $val) {
            $column               = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
            $this->date_columns[] = $column;
        }
        return $this;
    }

    public function numeric_column($columns)
    {
        foreach ($this->explode(',', $columns) as $val) {
            $column                  = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
            $this->numeric_columns[] = $column;
        }
        return $this;
    }
	
    public function checkbox_column($columns)
    {
        foreach ($this->explode(',', $columns) as $val) {
            $column                  = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
            $this->checkbox_columns[] = $column;
        }
        return $this;
    }





    /**
     * Unset column
     *
     * @param string $column
     * @return mixed
     */
    public function unset_column($column)
    {
        $this->unset_columns[] = $column;
        return $this;
    }

    /**
     * Builds all the necessary query segments and performs the main query based on results set from chained statements
     *
     * @param string charset
     * @return string
     */
    public function generate($charset = 'UTF-8')
    {
        $this->get_paging();
        $this->get_ordering();
        $this->get_filtering();
		
        return $this->produce_output($charset);
    }

    /**
     * Generates the LIMIT portion of the query
     *
     * @return mixed
     */
    protected function get_paging()
    {
        $iStart  = $this->ci->input->get('iDisplayStart');
        $iLength = $this->ci->input->get('iDisplayLength');
        $this->ci->db->limit(($iLength != '' && $iLength != '-1') ? $iLength : 10, ($iStart) ? $iStart : 0);
    }

    /**
     * Generates the ORDER BY portion of the query
     *
     * @return mixed
     */
    protected function get_ordering()
    {
        if ($this->check_mDataprop())
            $mColArray = $this->get_mDataprop();
        elseif ($this->ci->input->get('sColumns'))
            $mColArray = explode(',', $this->ci->input->get('sColumns'));
        else
            $mColArray = $this->columns;

        $mColArray = array_values(array_diff($mColArray, $this->unset_columns));
        $columns   = array_values(array_diff($this->columns, $this->unset_columns));

        for ($i = 0; $i < intval($this->ci->input->get('iSortingCols')); $i++)
            if (isset($mColArray[intval($this->ci->input->get('iSortCol_' . $i))]) && in_array($mColArray[intval($this->ci->input->get('iSortCol_' . $i))], $columns) && $this->ci->input->get('bSortable_' . intval($this->ci->input->get('iSortCol_' . $i))) == 'true')
                $this->ci->db->order_by($mColArray[intval($this->ci->input->get('iSortCol_' . $i))], $this->ci->input->get('sSortDir_' . $i));
    }

    /**
     * Generates the LIKE portion of the query
     *
     * @return mixed
     */
    protected function get_filtering()
    {
        if ($this->check_mDataprop())
            $mColArray = $this->get_mDataprop();
        elseif ($this->ci->input->get('sColumns'))
            $mColArray = explode(',', $this->ci->input->get('sColumns'));
        else
            $mColArray = $this->columns;

        $sWhere  = '';
        //irul
        //$sSearch = mysql_real_escape_string($this->ci->input->get('sSearch'));
        $sSearch = $this->ci->input->get('sSearch');

        $mColArray = array_values(array_diff($mColArray, $this->unset_columns));
        $columns   = array_values(array_diff($this->columns, $this->unset_columns));

		/*
        if ($sSearch != '')
            for ($i = 0; $i < count($mColArray); $i++)
                if ($this->ci->input->get('bSearchable_' . $i) == 'true' && in_array($mColArray[$i], $columns))
                    $sWhere .= $this->select[$mColArray[$i]] . " LIKE '%" . $sSearch . "%' OR ";
        */
        if ($sSearch != '') {
            for ($i = 0; $i < count($mColArray); $i++) {
                if ($this->ci->input->get('bSearchable_' . $i) == 'true' && in_array($mColArray[$i], $columns)) {
					if (@in_array($mColArray[$i], $date_columns)) {
						$sWhere .= "to_char(".$this->select[$mColArray[$i]] . ", 'dd-mm-yyyy') = '" . $sSearch . "' OR ";
					} else if (@in_array($mColArray[$i], $numeric_columns)) {
						$sWhere .= $this->select[$mColArray[$i]] . "::text = '" . $sSearch . "' OR ";
					} else if (@in_array($mColArray[$i], $rupiah_columns)) {
						$sWhere .= $this->select[$mColArray[$i]] . "::text = '" . $sSearch . "' OR ";
					} else {
						$sWhere .= "upper(".$this->select[$mColArray[$i]] . "::text) LIKE upper('%" . $sSearch . "%') OR ";
					}
				}
			}
		}

        $sWhere = substr_replace($sWhere, '', -3);

        if ($sWhere != '')
            $this->ci->db->where('(' . $sWhere . ')');

        for ($i = 0; $i < intval($this->ci->input->get('iColumns')); $i++) {
            if ($this->ci->input->get('sSearch_' . $i) && $this->ci->input->get('sSearch_' . $i) != '' && in_array($mColArray[$i], $columns)) {
                $miSearch = explode(',', $this->ci->input->get('sSearch_' . $i));
                foreach ($miSearch as $val) {
                    if (preg_match("/(<=|>=|=|<|>)(\s*)(.+)/i", trim($val), $matches))
                        $this->ci->db->where($this->select[$mColArray[$i]] . ' ' . $matches[1], $matches[3]);
                    else
                        $this->ci->db->where($this->select[$mColArray[$i]] . ' LIKE', '%' . $val . '%');
                }
            }
        }

        foreach ($this->filter as $val)
            $this->ci->db->where($val[0], $val[1], $val[2]);
    }

    /**
     * Compiles the select statement based on the other functions called and runs the query
     *
     * @return mixed
     */
    protected function get_display_result()
    {
        return $this->ci->db->get();
    }

    /**
     * Builds a JSON encoded string data
     *
     * @param string charset
     * @return string
     */
    protected function produce_output($charset)
    {
        $aaData         = array();
        $rResult        = $this->get_display_result();
        
		//irul
        $qry = 'select * from x;';
        if (defined('MY_ENV') && MY_ENV == 'development')
            $qry = $this->ci->db->last_query();

        $iTotal         = $this->get_total_results();
        $iFilteredTotal = $this->get_total_results(TRUE);

        foreach ($rResult->result_array() as $row_key => $row_val) {
            //echo ($aaData[$row_key]);

            $aaData[$row_key] = ($this->check_mDataprop()) ? $row_val : array_values($row_val);

            foreach ($this->add_columns as $field => $val)
                if ($this->check_mDataprop())
                    $aaData[$row_key][$field] = $this->exec_replace($val, $aaData[$row_key]);
                else
                    $aaData[$row_key][] = $this->exec_replace($val, $aaData[$row_key]);

            foreach ($this->edit_columns as $modkey => $modval)
                foreach ($modval as $val)
                    $aaData[$row_key][($this->check_mDataprop()) ? $modkey : array_search($modkey, $this->columns)] = $this->exec_replace($val, $aaData[$row_key]);




            $aaData[$row_key] = array_diff_key($aaData[$row_key], ($this->check_mDataprop()) ? $this->unset_columns : array_intersect($this->columns, $this->unset_columns));
            //echo $aaData[$row_key][1];

            if (!$this->check_mDataprop())
                $aaData[$row_key] = array_values($aaData[$row_key]);
        }

        $sColumns = array_diff($this->columns, $this->unset_columns);
        $sColumns = array_merge_recursive($sColumns, array_keys($this->add_columns));

		// irul
        for ($i = 0; $i < sizeof($aaData); $i++) {
            for ($j = 0; $j < sizeof($this->date_columns); $j++) {
                //$aaData[$i][$this->date_columns[$j]] = convert_date($aaData[$i][$this->date_columns[$j]]);
				if (!empty($aaData[$i][$this->date_columns[$j]]))
					$aaData[$i][$this->date_columns[$j]] = date('d-m-Y', strtotime($aaData[$i][$this->date_columns[$j]]));
				else
					$aaData[$i][$this->date_columns[$j]] = $aaData[$i][$this->date_columns[$j]];
            }

            for ($j = 0; $j < sizeof($this->rupiah_columns); $j++) {
                //$aaData[$i][$this->rupiah_columns[$j]] = rupiah($aaData[$i][$this->rupiah_columns[$j]]);
                $aaData[$i][$this->rupiah_columns[$j]] = number_format($aaData[$i][$this->rupiah_columns[$j]], 0, ',', '.');
            }
			
            for ($j = 0; $j < sizeof($this->checkbox_columns); $j++) {
                //$aaData[$i][$this->checkbox_columns[$j]] = '<input type="checkbox" name="enabled" '.($aaData[$i][$this->checkbox_columns[$j]] == 1 ? 'checked' : '').' >';
                $aaData[$i][$this->checkbox_columns[$j]] = $aaData[$i][$this->checkbox_columns[$j]] == 1 ? '<i class="icon-ok"></i>' : '';
            }
        }
        $sOutput = array(
			'aSQL' => $qry,
            'sEcho' => intval($this->ci->input->get('sEcho')),
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFilteredTotal,
            'aaData' => $aaData
        );

        if (strtolower($charset) == 'utf-8')
            return json_encode($sOutput);
        else
            return $this->jsonify($sOutput);
    }

    /**
     * Get result count
     *
     * @return integer
     */
    protected function get_total_results($filtering = FALSE)
    {
        if ($filtering)
            $this->get_filtering();

        foreach ($this->joins as $val)
            $this->ci->db->join($val[0], $val[1], $val[2]);

        foreach ($this->where as $val)
            $this->ci->db->where($val[0], $val[1], $val[2]);

        return $this->ci->db->count_all_results($this->table);
    }

    /**
     * Runs callback functions and makes replacements
     *
     * @param mixed $custom_val
     * @param mixed $row_data
     * @return string $custom_val['content']
     */
    protected function exec_replace($custom_val, $row_data)
    {
        $replace_string = '';

        if (isset($custom_val['replacement']) && is_array($custom_val['replacement'])) {
            foreach ($custom_val['replacement'] as $key => $val) {
                $sval = preg_replace("/(?<!\w)([\'\"])(.*)\\1(?!\w)/i", '$2', trim($val));
                if (preg_match('/(\w+)\((.*)\)/i', $val, $matches) && function_exists($matches[1])) {
                    $func = $matches[1];
                    $args = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[,]+/", $matches[2], 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                    foreach ($args as $args_key => $args_val) {
                        $args_val        = preg_replace("/(?<!\w)([\'\"])(.*)\\1(?!\w)/i", '$2', trim($args_val));
                        $args[$args_key] = (in_array($args_val, $this->columns)) ? ($row_data[($this->check_mDataprop()) ? $args_val : array_search($args_val, $this->columns)]) : $args_val;
                    }

                    $replace_string = call_user_func_array($func, $args);
                } elseif (in_array($sval, $this->columns))
                    $replace_string = $row_data[($this->check_mDataprop()) ? $sval : array_search($sval, $this->columns)];
                else
                    $replace_string = $sval;

                $custom_val['content'] = str_ireplace('$' . ($key + 1), $replace_string, $custom_val['content']);
            }
        }

        return $custom_val['content'];
    }

    /**
     * Check mDataprop
     *
     * @return bool
     */
    protected function check_mDataprop()
    {
        if (!$this->ci->input->get('mDataProp_0'))
            return FALSE;

        for ($i = 0; $i < intval($this->ci->input->get('iColumns')); $i++)
            if (!is_numeric($this->ci->input->get('mDataProp_' . $i)))
                return TRUE;

        return FALSE;
    }

    /**
     * Get mDataprop order
     *
     * @return mixed
     */
    protected function get_mDataprop()
    {
        $mDataProp = array();

        for ($i = 0; $i < intval($this->ci->input->get('iColumns')); $i++)
            $mDataProp[] = $this->ci->input->get('mDataProp_' . $i);

        return $mDataProp;
    }

    /**
     * Return the difference of open and close characters
     *
     * @param string $str
     * @param string $open
     * @param string $close
     * @return string $retval
     */
    protected function balanceChars($str, $open, $close)
    {
        $openCount  = substr_count($str, $open);
        $closeCount = substr_count($str, $close);
        $retval     = $openCount - $closeCount;
        return $retval;
    }

    /**
     * Explode, but ignore delimiter until closing characters are found
     *
     * @param string $delimiter
     * @param string $str
     * @param string $open
     * @param string $close
     * @return mixed $retval
     */
    protected function explode($delimiter, $str, $open = '(', $close = ')')
    {
        $retval  = array();
        $hold    = array();
        $balance = 0;
        $parts   = explode($delimiter, $str);

        foreach ($parts as $part) {
            $hold[] = $part;
            $balance += $this->balanceChars($part, $open, $close);
            if ($balance < 1) {
                $retval[] = implode($delimiter, $hold);
                $hold     = array();
                $balance  = 0;
            }
        }

        if (count($hold) > 0)
            $retval[] = implode($delimiter, $hold);

        return $retval;
    }

    /**
     * Workaround for json_encode's UTF-8 encoding if a different charset needs to be used
     *
     * @param mixed result
     * @return string
     */
    protected function jsonify($result = FALSE)
    {
        if (is_null($result))
            return 'null';
        if ($result === FALSE)
            return 'false';
        if ($result === TRUE)
            return 'true';

        if (is_scalar($result)) {
            if (is_float($result))
                return floatval(str_replace(',', '.', strval($result)));

            if (is_string($result)) {
                static $jsonReplaces = array(array('\\', '/', '\n', '\t', '\r', '\b', '\f', '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $result) . '"';
            } else
                return $result;
        }

        $isList = TRUE;

        for ($i = 0, reset($result); $i < count($result); $i++, next($result)) {
            if (key($result) !== $i) {
                $isList = FALSE;
                break;
            }
        }

        $json = array();

        if ($isList) {
            foreach ($result as $value)
                $json[] = $this->jsonify($value);

            return '[' . join(',', $json) . ']';
        } else {
            foreach ($result as $key => $value)
                $json[] = $this->jsonify($key) . ':' . $this->jsonify($value);

            return '{' . join(',', $json) . '}';
        }
    }
}
/* End of file Datatables.php */
/* Location: ./application/libraries/Datatables.php */