<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Report.php");

class Specific_discount extends Report
{
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table($inputs);
	}

	public function getDataColumns()
	{
		return array(
			'summary' => array(
				array('id' => lang('reports_sale_id')),
				array('type_code' => lang('reports_code_type')),
				array('sale_date' => lang('reports_date'), 'sortable' => FALSE),
				array('quantity' => lang('reports_quantity')),
				array('employee_name' => lang('reports_sold_by')),
				array('customer_name' => lang('reports_sold_to')),
				array('subtotal' => lang('reports_subtotal'), 'sorter' => 'number_sorter'),
				array('tax' => lang('reports_tax'), 'sorter' => 'number_sorter'),
				array('total' => lang('reports_total'), 'sorter' => 'number_sorter'),
				array('cost' => lang('reports_cost'), 'sorter' => 'number_sorter'),
				array('profit' => lang('reports_profit'), 'sorter' => 'number_sorter'),
				array('payment_type' => lang('reports_payment_type'), 'sortable' => FALSE),
				array('comment' => lang('reports_comments'))),
			'details' => array(
				lang('reports_name'),
				lang('reports_category'),
				lang('reports_item_number'),
				lang('reports_description'),
				lang('reports_quantity'),
				lang('reports_subtotal'),
				lang('reports_tax'),
				lang('reports_total'),
				lang('reports_cost'),
				lang('reports_profit'),
				lang('reports_discount')),
			'details_rewards' => array(
				lang('reports_used'),
				lang('reports_earned'))
		);
	}

	public function getData(array $inputs)
	{
		$this->db->select('sale_id,
			MAX(CASE
			WHEN sale_type = ' . SALE_TYPE_POS . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_pos') . '\'
			WHEN sale_type = ' . SALE_TYPE_INVOICE . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_invoice') . '\'
			WHEN sale_type = ' . SALE_TYPE_WORK_ORDER . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('reports_code_work_order') . '\'
			WHEN sale_type = ' . SALE_TYPE_QUOTE . ' && sale_status = ' . SUSPENDED . ' THEN \'' . lang('reports_code_quote') . '\'
			WHEN sale_type = ' . SALE_TYPE_RETURN . ' && sale_status = ' . COMPLETED . ' THEN \'' . lang('reports_code_return') . '\'
			WHEN sale_status = ' . CANCELED . ' THEN \'' . lang('reports_code_canceled') . '\'
			ELSE \'\'
			END) AS type_code,
			MAX(sale_status) as sale_status,
			MAX(sale_date) AS sale_date,
			SUM(quantity_purchased) AS items_purchased,
			MAX(employee_name) AS employee_name,
			MAX(customer_name) AS customer_name,
			SUM(subtotal) AS subtotal,
			SUM(tax) AS tax,
			SUM(total) AS total,
			SUM(cost) AS cost,
			SUM(profit) AS profit,
			MAX(payment_type) AS payment_type,
			MAX(comment) AS comment');
		$this->db->from('sales_items_temp');

		$this->db->where('discount >=', $inputs['discount']);
		$this->db->where('discount_type', $inputs['discount_type']);

		if($inputs['sale_type'] == 'complete')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$this->db->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->where('sale_type', SALE_TYPE_RETURN);
		}

		$this->db->group_by('sale_id');
		$this->db->order_by('MAX(sale_date)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		$data['rewards'] = array();

		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, item_number, description, quantity_purchased, subtotal, tax, total, cost, profit, discount, discount_type');
			$this->db->from('sales_items_temp');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
			$this->db->select('used, earned');
			$this->db->from('sales_reward_points');
			$this->db->where('sale_id', $value['sale_id']);
			$data['rewards'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(tax) AS tax, SUM(total) AS total, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');

		$this->db->where('discount >=', $inputs['discount']);
		$this->db->where('discount_type', $inputs['discount_type']);

		if($inputs['sale_type'] == 'complete')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sale_type', SALE_TYPE_RETURN);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'sales')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->group_start();
			$this->db->where('sale_type', SALE_TYPE_POS);
			$this->db->or_where('sale_type', SALE_TYPE_INVOICE);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$this->db->where('sale_status', SUSPENDED);
			$this->db->where('sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$this->db->where('sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sale_status', COMPLETED);
			$this->db->where('sale_type', SALE_TYPE_RETURN);
		}

		return $this->db->get()->row_array();
	}
}
?>
