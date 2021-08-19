<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dinner_table class
 */

class Dinner_table extends Model
{
	public function exists($dinner_table_id): bool
	{
		$builder = $this->db->table('dinner_tables');
		$builder->where('dinner_table_id', $dinner_table_id);

		return ($builder->get()->getNumRows() >= 1);
	}
//TODO: need to fix this function so it either isn't overriding the basemodel function or get it in line
	public function save($table_data, $dinner_table_id): bool
	{
		$table_data_to_save = array('name' => $table_data['name'], 'deleted' => 0);

		$builder = $this->db->table('dinner_tables');
		if(!$this->exists($dinner_table_id))
		{
			return $builder->insert($table_data_to_save);
		}

		$builder->where('dinner_table_id', $dinner_table_id);

		return $builder->update($table_data_to_save);
	}

	/**
	Get empty tables
	*/
	public function get_empty_tables($current_dinner_table_id): array
	{
		$builder = $this->db->table('dinner_tables');
		$builder->where('status', 0);
		$builder->orWhere('dinner_table_id', $current_dinner_table_id);
		$builder->where('deleted', 0);

		$empty_tables = $builder->get()->getResultArray();

		$empty_tables_array = array();
		foreach($empty_tables as $empty_table)
		{
			$empty_tables_array[$empty_table['dinner_table_id']] = $empty_table['name'];
		}

		return $empty_tables_array;
	}

	public function get_name($dinner_table_id): string
	{
		if(empty($dinner_table_id))
		{
			return '';
		}
		else
		{
			$builder = $this->db->table('dinner_tables');
			$builder->where('dinner_table_id', $dinner_table_id);

			return $builder->get()->getRow()->name;
		}
	}

	public function is_occupied($dinner_table_id): bool
	{
		if(empty($dinner_table_id))
		{
			return FALSE;
		}
		else
		{
			$builder = $this->db->table('dinner_tables');
			$builder->where('dinner_table_id', $dinner_table_id);

			return ($builder->get()->getRow()->status == 1);
		}
	}

	public function get_all()
	{
		$builder = $this->db->table('dinner_tables');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	/**
	* Deletes one dinner table
	*/
	public function delete($dinner_table_id): bool
	{
		$builder = $this->db->table('dinner_tables');
		$builder->where('dinner_table_id', $dinner_table_id);

		return $builder->update(['deleted' => 1]);
	}

	/**
	 * Occupy table
	 * Ignore the Delivery and Takeaway "tables".  They should never be occupied.
	 */
	public function occupy($dinner_table_id): bool
	{
		if($dinner_table_id > 2 )
		{
			$builder = $this->db->table('dinner_tables');
			$builder->where('dinner_table_id', $dinner_table_id);
			return $builder->update(['status' => 1]);
		}
		else//TODO: THIS ELSE STATEMENT ISN'T NEEDED.  JUST DO THE IF AND THEN RETURN TRUE AFTER IT.
		{
			return true;
		}
	}

	/**
	Release table
	 */
	public function release($dinner_table_id): bool
	{
		if($dinner_table_id > 2 )
		{
			$builder = $this->db->table('dinner_tables');
			$builder->where('dinner_table_id', $dinner_table_id);
			return $builder->update(['status' => 0]);
		}
		else
		{
			return true;
		}
	}

	/**
	Swap tables
	 */
	public function swap_tables($release_dinner_table_id, $occupy_dinner_table_id): bool
	{
		return $this->release($release_dinner_table_id) && $this->occupy($occupy_dinner_table_id);
	}
}
?>
