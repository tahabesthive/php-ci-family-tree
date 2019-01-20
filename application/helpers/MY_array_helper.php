<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('relation_builder'))
{     
    /**
    * relation builder basis of parent, daughters & subsister logic
    *
    * @param array $list
    * @param array $records
    * @param string $name
    *
    * @return array
    */

    function relation_builder($list,$records,$name = false){
        $listnew = [];
        $sameLevel = [];
        $duplicate = [];
        if($name){
            $id = searchArrayValueByKey($records,$name);
        } 
        if(is_array($list)){
            $currentname = $name;
            foreach($list as $value){
                if(isset($value['org_name'])){
                    $currentname = $value['org_name'];
                    $next_id = searchArrayValueByKey($records,$value['org_name']);
                    $sameLevel[] = $next_id;
                    $listnew[] = ["organization_id" => $id , "relation_type_id"=> 1, "organization_rel_id" => $next_id];
                    $listnew[] = ["organization_id" => $next_id, "relation_type_id"=> 2, "organization_rel_id" => $id ];
                }
                if(isset($value['daughters'])){
                    $listnew = array_merge($listnew,relation_builder($value['daughters'],$records,$currentname));
                }
            }
            $reverse = $sameLevel;
            if(isset($value['org_name'])){
                foreach(array_unique($sameLevel) as $sister){
                    krsort($reverse);
                    foreach( $reverse as $subsister){
                        if($sister != $subsister && !in_array($sister, $duplicate)) $listnew[] = ["organization_id" => $sister, "relation_type_id"=> 3, "organization_rel_id" => $subsister ];
                        $duplicate[] = $sister; 
                    }
                }
            }
            
        }

        return $listnew;
    }
}

if ( ! function_exists('organization_relation_list_check_create'))
{

    /**
    * verify decode array list of relation which has to be pass to relation builder basis of parent, daughters & subsister logic
    *
    * @param array $list
    * @param string $name
    *
    * @return array or boolean
    */

    function organization_relation_list_check_create($list,$name = false){
        $listnew = [];
        if($name) $listnew[] = $name;
        if(is_array($list)){
            foreach($list as $key => $value){
                
                foreach($value as $sk => $sv){
                    if($sk != 'org_name' && $sk != 'daughters') return false;
                }

                if(isset($value['org_name'])){
                    if(is_string($value['org_name'])){
                        $listnew[] = $value['org_name'];
                    }else{
                        return false;
                    }
                }
                if(isset($value['daughters'])){
                    if(is_array($value['daughters'])){
                        $link = organization_relation_list_check_create($value['daughters']);
                        if($link){
                            $listnew = array_merge($listnew,organization_relation_list_check_create($value['daughters']));
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
        return array_values(array_unique($listnew));
    }
}

if ( ! function_exists('filter_array_by'))
{

    /**
    * filter the array with given columns, default columns name is name
    *
    * @param array $list
    * @param string $col
    *
    * @return array 
    */  

    function filter_by( $list, $col = 'name'){
        $allNode = [];
        if(is_array($list)){
            foreach($list as $value){
                $allNode[] = $value[$col];
            }
        }
        return $allNode;
    }
}

if ( ! function_exists('filter_by_key'))
{
    /**
    * filter the array by given key and mount all values, default columns name is name
    *
    * @param array $list
    * @param string $col
    *
    * @return array 
    */  

    function filter_by_key($list,$col = 'name'){
        $allNode = [];
        if(is_array($list)){
            foreach($list as $value){
                $allNode[][$col] = $value;
            }
        }        
        return array_filter($allNode);
    }
}

if ( ! function_exists('filter_by_id_value'))
{
    /**
    * filter the array by id and selected value of columns, default columns name is name
    *
    * @param array $list
    * @param string $col
    *
    * @return array 
    */  
    function filter_by_id_value($list,$col='name'){
        $allNode = [];
        if(is_array($list)){
            foreach($list as $key=> $value){
                $allNode[$value['id']] = $value[$col];
            }
        }
        return $allNode;
    }
}

if ( ! function_exists('implode_by_organization_id'))
{
    /**
    * implode the list to predefine column selection as string for mysql IN query.
    *
    * @param array $list
    * @param string $col
    *
    * @return string
    */  
    function implode_by_organization_id($list){
        return implode(',', array_map(function ($entry) {
            return $entry['organization_id']; 
        }, $list));
    }
}

if ( ! function_exists('searchArrayValueByKey'))
{
    /**
    * search in the array value by key with iterator
    *
    * @param array $array
    * @param string $search
    *
    * @return array or string or false
    */  
    function searchArrayValueByKey(array $array, $search) {
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key => $value) {
            if ($search === $value)
            return $key;
        }
        return false;
    }
}