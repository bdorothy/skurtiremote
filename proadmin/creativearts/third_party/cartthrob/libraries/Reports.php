<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports
{
	private $default_status;
	private $failed_status;
	private $declined_status;
	private $processing_status;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->model('cartthrob_settings_model');
		
		$this->EE->load->helper('calendar');
		
		$this->EE->load->model('order_model');
		
		// @TODO whenever we get more statuses set in the config, need to make this list dynamic
		$this->default_status = ($this->EE->config->item('cartthrob:orders_default_status')) ? $this->EE->config->item('cartthrob:orders_default_status') : 'open';

		$this->failed_status = ($this->EE->config->item('cartthrob:orders_failed_status')) ? $this->EE->config->item('cartthrob:orders_failed_status') : 'closed';
		$this->declined_status = ($this->EE->config->item('cartthrob:orders_declined_status')) ? $this->EE->config->item('cartthrob:orders_declined_status') : 'closed';
		$this->processing_status = ($this->EE->config->item('cartthrob:orders_processing_status')) ? $this->EE->config->item('cartthrob:orders_processing_status') : 'closed';
		$this->status_pending = ($this->EE->config->item('cartthrob:orders_status_pending')) ? $this->EE->config->item('cartthrob:orders_status_pending') : 'closed';
		$this->status_expired = ($this->EE->config->item('cartthrob:orders_status_expired')) ? $this->EE->config->item('cartthrob:orders_status_expired') : 'closed';
		$this->status_canceled = ($this->EE->config->item('cartthrob:orders_status_canceled')) ? $this->EE->config->item('cartthrob:orders_status_canceled') : 'closed';
		$this->status_voided = ($this->EE->config->item('cartthrob:orders_status_voided')) ? $this->EE->config->item('cartthrob:orders_status_voided') : 'closed';
		$this->status_refunded = ($this->EE->config->item('cartthrob:orders_status_refunded')) ? $this->EE->config->item('cartthrob:orders_status_refunded') : 'closed';
		$this->status_reversed = ($this->EE->config->item('cartthrob:orders_status_reversed')) ? $this->EE->config->item('cartthrob:orders_status_reversed') : 'closed';
		$this->status_offsite = ($this->EE->config->item('cartthrob:orders_status_offsite')) ? $this->EE->config->item('cartthrob:orders_status_offsite') : 'closed';
		
		$this->ignored_statuses = array(
			$this->declined_status   ,
		    $this->processing_status ,
			$this->failed_status	 ,
		    $this->status_reversed   ,
		    $this->status_refunded   ,
		    $this->status_voided     ,
		    $this->status_canceled   ,
		    $this->status_expired    ,
		    $this->status_pending    ,
			$this->status_offsite    ,
		); 
	}
 
	public function get_current_day_total()
	{
		//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
		$this->EE->db->where_not_in('status', $this->ignored_statuses);
	 	
		return $this->EE->order_model->order_totals(
			array(
				'year' => date('Y'),
				'month' => date('m'),
				'day' => date('d'),
			),
			TRUE
		);
	}
	
	public function get_current_month_total()
	{
		//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
		$this->EE->db->where_not_in('status', $this->ignored_statuses);
		
		return $this->EE->order_model->order_totals(
			array(
				'year' => date('Y'),
				'month' => date('m'),
				),
			TRUE
		);
	}
	
	public function get_current_year_total()
	{
		//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
		$this->EE->db->where_not_in('status', $this->ignored_statuses);
		
		return $this->EE->order_model->order_totals(
			array(
				'year' => date('Y'),
				),
			TRUE
		);
	}
	
	public function get_yearly_totals($year)
	{
		$rows = array();
		
		for ($i = 1; $i <= 12; $i++)
		{
			$month = ($i < 10) ? '0'.$i : $i;
			
			//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
			$this->EE->db->where_not_in('status', $this->ignored_statuses);
			
			$data = $this->EE->order_model->order_totals(array(
				'year' => $year,
				'month' => $month,
			));
			
			$rows[] = array(
				'subtotal' => $data['subtotal'],
				'tax' => $data['tax'],
				'shipping' => $data['shipping'],
				'discount' => $data['discount'],
				'total' => $data['total'],
				'date' => $month.$year,
				'name' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
				'href' => 'month='.$month.'&year='.$year,
			);
		}
		return $rows;
	}
	
	public function get_monthly_totals($month, $year)
	{
		//@TODO make this use any status other than processing, declined, failed CT statuses. 
		
		$rows = array();
		
		$days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		
		for ($i = 1; $i <= $days; $i++)
		{
			$day = ($i < 10) ? '0'.$i : $i;
			
			//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
			$this->EE->db->where_not_in('status', $this->ignored_statuses);
			
			$data = $this->EE->order_model->order_totals(array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
			));
			
			$rows[] = array(
				'subtotal' => $data['subtotal'],
				'tax' => $data['tax'],
				'shipping' => $data['shipping'],
				'discount' => $data['discount'],
				'total' => $data['total'],
				'date' => $day,
				'name' => date('D d', mktime (0, 0, 0, $month, $i, $year)),
				'href' => 'month='.$month.'&year='.$year.'&day='.$day,
			);
		}
		
		return $rows;
	}
	
	public function get_daily_totals($day, $month, $year)
	{
		$rows = array();
		//@TODO fix: if there's no order channel installed... this will cause errors in the reports tab
		
		$orders = $this->EE->order_model->get_orders(array('year' => $year, 'month' => $month, 'day' => $day));
		
		foreach ($orders as $order)
		{	
			$rows[] = array(
				'subtotal' => ($this->EE->config->item('cartthrob:orders_subtotal_field')) ? $order['field_id_'.$this->EE->config->item('cartthrob:orders_subtotal_field')] : 0,
				'tax' => ($this->EE->config->item('cartthrob:orders_tax_field')) ? $order['field_id_'.$this->EE->config->item('cartthrob:orders_tax_field')] : 0,
				'shipping' => ($this->EE->config->item('cartthrob:orders_shipping_field')) ? $order['field_id_'.$this->EE->config->item('cartthrob:orders_shipping_field')] : 0,
				'discount' => ($this->EE->config->item('cartthrob:orders_discount_field')) ? $order['field_id_'.$this->EE->config->item('cartthrob:orders_discount_field')] : 0,
				'total' => ($this->EE->config->item('cartthrob:orders_total_field')) ? $order['field_id_'.$this->EE->config->item('cartthrob:orders_total_field')] : 0,
				'date' => $order['entry_date'],
				'name' => date('g:ia', $order['entry_date']),
				'href' => 'entry_id='.$order['entry_id'],
			);
		}
		
		return $rows;
	}
	
	public function get_all_totals($start = NULL, $end = NULL)
	{
		$rows = array();
		
		if (!$start)
		{
			$this->EE->db->where_not_in('status', $this->ignored_statuses);
			$start = $this->EE->db->select('entry_date')
						->limit(1)
						->where('channel_id', $this->EE->config->item('cartthrob:orders_channel'))
						->order_by('entry_date', 'asc')
						->get('channel_titles')
						->row('entry_date');
		}

		if (!$end)
		{
			$this->EE->db->where_not_in('status', $this->ignored_statuses);
			$end = $this->EE->db->select('entry_date')
						->limit(1)
						->where('channel_id', $this->EE->config->item('cartthrob:orders_channel'))
						->order_by('entry_date', 'desc')
						->get('channel_titles')
						->row('entry_date');

		}

		if ($start && $end)
		{
			$start = getdate($start);
			$end = getdate($end);
			
			$totals = array();
			
			for ($year = $start['year']; $year <= $end['year']; $year++)
			{
				for ($month = ($year == $start['year']) ? $start['mon'] : 1; $month <= (($year == $end['year']) ? $end['mon'] : 12); $month++)
				{
					$this->EE->db->where_not_in('status', $this->ignored_statuses);

					$totals[$year][$month] = $this->EE->order_model->order_totals(array(
						'entry_date >=' => mktime(0, 0, 0, $month, 1, $year),
						'entry_date <' => mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year),
					));
				}
			}
		
			foreach ($totals as $year => $months)
			{
				foreach ($months as $month => $data)
				{
					$month = ($month < 10) ? '0'.$month : $month;
					
					$rows[] = array(
						'subtotal' => $data['subtotal'],
						'tax' => $data['tax'],
						'shipping' => $data['shipping'],
						'discount' => $data['discount'],
						'total' => $data['total'],
						'date' => $month.$year,
						'name' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
						'href' => 'month='.$month.'&year='.$year,
					);
				}
			}
		}
		
		return $rows;
	}
}