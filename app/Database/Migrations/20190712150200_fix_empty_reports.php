<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_fix_empty_reports extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$builder->select('location_name');
		$builder = $this->db->table('stock_locations');
		$builder->where('location_id', 1);
		$builder->limit(1);
		$location_name = $builder->get()->getResultArray()[0]['location_name'];

		$location_name = str_replace(' ', '_', $location_name);
		$this->db->set('location_id',1);
		$builder->where('permission_id','receivings_' . $location_name);
		$builder->orWhere('permission_id', 'sales_' . $location_name);
		$builder->update('permissions');
	}

	public function down()
	{

	}
}
?>