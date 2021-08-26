<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Module class
 */

class Module extends Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function get_module_name($module_id): string
	{
		$builder = $this->db->table('modules');
		$query = $builder->getWhere(['module_id' => $module_id], 1);

		if($query->getNumRows() == 1)
		{
			$row = $query->getRow();

			return lang($row->name_lang_key);
		}

		return lang('Error.unknown');
	}

	public function get_module_desc($module_id): string
	{
		$builder = $this->db->table('modules');
		$query = $builder->getWhere(['module_id' => $module_id], 1);

		if($query->getNumRows() == 1)
		{
			$row = $query->getRow();

			return lang($row->desc_lang_key);
		}

		return lang('Error.unknown');
	}

	public function get_all_permissions()
	{
		$builder = $this->db->table('permissions');

		return $builder->get();
	}

	public function get_all_subpermissions()
	{
		$builder = $this->db->table('permissions');
		$builder->join('modules AS modules', 'modules.module_id = permissions.module_id');

		// can't quote the parameters correctly when using different operators..
		$builder->where('modules.module_id != ', 'permission_id', FALSE);

		return $builder->get();
	}

	public function get_all_modules()
	{
		$builder = $this->db->table('modules');
		$builder->orderBy('sort', 'asc');
		return $builder->get();
	}

	public function get_allowed_home_modules($person_id)
	{
		$menus = array('home', 'both');
		$builder = $this->db->table('modules');	//TODO: this is duplicated with the code below... probably refactor a method and just pass through whether home/office modules are needed.
		$builder->join('permissions', 'permissions.permission_id = modules.module_id');
		$builder->join('grants', 'permissions.permission_id = grants.permission_id');
		$builder->where('person_id', $person_id);
		$builder->whereIn('menu_group', $menus);
		$builder->where('sort !=', 0);
		$builder->orderBy('sort', 'asc');
		return $builder->get();
	}

	public function get_allowed_office_modules($person_id)
	{
		$menus = array('office', 'both');
		$builder = $this->db->table('modules');
		$builder->join('permissions', 'permissions.permission_id = modules.module_id');
		$builder->join('grants', 'permissions.permission_id = grants.permission_id');
		$builder->where('person_id', $person_id);
		$builder->whereIn('menu_group', $menus);
		$builder->where('sort !=', 0);
		$builder->orderBy('sort', 'asc');
		return $builder->get();
	}

	/**
	 * This method is used to set the show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function set_show_office_group($show_office_group)
	{
		if($show_office_group)
		{
			$sort = 999;
		}
		else
		{
			$sort = 0;
		}

		$modules_data = array(
			'sort' => $sort
		);

		$builder = $this->db->table('modules');
		$builder->where('module_id', 'office');
		$builder->update($modules_data);
	}

	/**
	 * This method is used to show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function get_show_office_group()
	{
		$builder = $this->db->table('grants');
		$builder->select('sort');
		$builder->where('module_id', 'office');
		$builder = $this->db->table('modules');
		return $builder->get()->getRow()->sort;
	}
}
?>