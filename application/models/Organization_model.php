<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_model extends CI_Model {

    // You must always define the table and the primary key
    public $table = 'organization';
    public $relationship_type_table = 'organization_relation_type';
    public $organization_relation_index_table = 'organization_relation_index';

    public function search($keyword = false, $start = 0,$limit = 10, $sort = 'asc'){

        $result = false;    
        if($keyword){
        // search the database with keyword.
        $this->db
        ->select('id')
        ->from($this->table)
        ->where('name', $keyword);
        $subQuery =  $this->db->get_compiled_select();
        
        // find sub-relationship and merge the compiled sub-query.
        $result =  $this->db
		->select('ot.title as relationship_type, o.name as org_name')
		->from("$this->table o")
		->join("$this->organization_relation_index_table oi",'oi.organization_id = o.id','left')
        ->join("$this->relationship_type_table ot",'ot.id = oi.relation_type_id','left')
        ->where("oi.organization_rel_id IN ($subQuery)", NULL, FALSE)
        ->group_by('o.id')
		->limit($limit,$start)
		->order_by('name', $sort)
		->get()
        ->result_object();
        }

        return $result;

    }

    public function organizaton_relations($records){     
        $list = relation_builder($this->input->post('daughters'),$records,$this->input->post('org_name'));

        // Remove old links.
        $this->db->where('organization_id IN ('.implode_by_organization_id($list,'organization_id').')');
        $this->db->delete($this->organization_relation_index_table);

        // Update new links for first relation level
        return $this->db->insert_batch($this->organization_relation_index_table, $list); 

    }

    public function import_organizations(){

        $finalRecords = false;
        // Filter all unique organization name with recursive loop
        $list = organization_relation_list_check_create($this->input->post('daughters'),$this->input->post('org_name'));       
        if(!$list) return false;

        if(is_array($list)){

            //find records in database with list.
            $this->db->select('id,name')->from($this->table);
            foreach($list as $name){
                $this->db->or_where('name', $name);
            }
            $oldRecords = $this->db->get()->result_array();

            if(count($oldRecords) > 0){
                $oldRecordsMerge  = filter_by_id_value($oldRecords);
                // Find and match the record if doesn't exists create the list to insert.
                $result = filter_by($oldRecords);       
                $addNewRecords = array_values(array_diff($list, $result));
            }else{
                $addNewRecords = array_values($list);
            }

            //insert organization name which was not in database with bulk query
            if(count($addNewRecords) > 0){
                $this->db->insert_batch($this->table, filter_by_key($addNewRecords,'name')); 

                //fine records in database with list.
                $this->db->select('id,name')->from($this->table);
                foreach($addNewRecords as $name){
                    $this->db->or_where('name', $name);
                }
                $newRecords = filter_by_id_value($this->db->get()->result_array());

                if(count($oldRecords) > 0){
                    $finalRecords = array_merge($oldRecordsMerge,$newRecords);
                }else{
                    $finalRecords = $newRecords;
                }
                
            }else{
                $finalRecords = $oldRecordsMerge;    
            } 
        }
        return $finalRecords;
    }

}