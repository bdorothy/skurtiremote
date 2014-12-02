<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (class_exists(basename(__FILE__, '.php'))) return;

/**
 * CartThrob Variables
 *
 * @package CartThrob2
 */
class Cartthrob_variables
{
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	public function add_encoded_option_vars(&$variables)
	{
		if ( ! isset($this->EE->TMPL))
		{
			$this->EE->load->library('template', NULL, 'TMPL');
		}

		if ( ! is_array($this->EE->TMPL->tagparams))
		{
			return ;
		}

		$options = array();

		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			if (strncmp($key, 'options:', 8) === 0)
			{
				$options[substr($key, 8)] = param_string_to_array($value);
			}
		}

		if ( ! preg_match_all('/{options:([^\s]*?)(.*?)?}(.*?){\/options:\\1}/s', $this->EE->TMPL->tagdata, $matches))
		{
			return;
		}

		foreach ($matches[0] as $i => $full_match)
		{
			$name = $matches[1][$i];

			$params = $matches[2][$i] ? $this->EE->functions->assign_parameters($matches[2][$i]) : array();

			$tagdata = $matches[3][$i];

			if (isset($options[$name]))
			{
				$option_data = array();

				$count = 1;

				$total_results = count($options[$name]);

				foreach ($options[$name] as $key => $value)
				{
					$selected = isset($params['selected']) && $params['selected'] == $key;

					$option_data[] = array(
						'option_value' => $this->EE->encrypt->encode($key),
						'option_name' => $value,
						'selected' => $selected ? ' selected="selected"' : '',
						'checked' => $selected ? ' checked="checked"' : '',
						'first_row' => $count === 1,
						'last_row' => $count === $total_results,
					);

					$count++;
				}

				$variables[substr($full_match, 1, -1)] = $this->EE->TMPL->parse_variables($tagdata, $option_data);
			}
		}
	}
	
	public function item_vars(Cartthrob_item $item, $global_vars = array(), $tagdata = FALSE, $prefix = '')
	{
		$this->EE->load->model(array('cartthrob_entries_model', 'product_model'));
		
		if ($tagdata === FALSE)
		{
			$tagdata = (isset($this->EE->TMPL)) ? $this->EE->TMPL->tagdata : '';
		}
		
		preg_match_all('/'.LD.'('.preg_quote($prefix).')?row_id_path=([\042\047]?)(.*)\\2?'.RD.'/', $tagdata, $row_id_paths);
		
		$vars = $global_vars;
		
		foreach ($this->item_option_vars($item->product_id(), $item->row_id()) as $key => $value)
		{
			$vars[$key] = $value;
		}
		
		$product = ($item->product_id()) ? $this->EE->product_model->get_product($item->product_id()) : FALSE;
		
		if ($product)
		{
			$vars = array_merge($vars, $this->EE->cartthrob_entries_model->entry_vars($product, $tagdata, $prefix));
		}

		if (is_array($item->item_options()))
		{
			foreach ($item->item_options() as $key => $value)
			{
				$vars[$prefix.'item_options:'.$key] = (is_array($value)) ? implode('|', $value) : $value;
			}
		}
		
		$vars[$prefix.'row_id'] = $item->row_id();
		$vars[$prefix.'entry_id'] = $item->product_id();
		$vars[$prefix.'title'] = $item->title();
		$vars[$prefix.'inventory'] = $item->inventory();
		$vars[$prefix.'quantity'] = $item->quantity();
		$vars[$prefix.'no_tax'] = (int) ! $item->is_taxable();
		$vars[$prefix.'no_shipping'] = (int) ! $item->is_shippable();

		//prefix tags with item_ incase of naming/parse order conflicts
		$vars[$prefix.'item_price_numeric'] = 
		$vars[$prefix.'price_numeric'] = 
		$vars[$prefix.'item_price:numeric'] = 
		$vars[$prefix.'price:numeric'] =  
			$item->price();
		
		$vars[$prefix.'item_price_plus_tax_numeric'] = 
		$vars[$prefix.'price_plus_tax_numeric']  = 
		$vars[$prefix.'item_price_plus_tax:numeric'] = 
		$vars[$prefix.'price_plus_tax:numeric'] =  
			$item->taxed_price(); 

		$vars[$prefix.'item_subtotal'] = $this->EE->number->format($item->price_subtotal());
		if ($item->meta('subscription'))
		{
			if (is_array($item->meta('subscription_options')))
			{
				foreach ($item->meta('subscription_options') as $key => $value)
				{
					$vars[$prefix.'subscription_'.$key] = $value;
				}
			}
			
			$vars[$prefix.'subscription'] = 1;
		}
		else
		{
			$vars[$prefix.'subscription'] = 0;
		}
		
		$vars[$prefix.'item_subtotal:plus_tax'] = 
		$vars[$prefix.'item_subtotal_plus_tax'] = 
			$this->EE->number->format($item->taxed_price_subtotal());

		$vars[$prefix.'item_tax'] = 
 			$this->EE->number->format( $item->tax() );

		$vars[$prefix.'item_total_tax'] = 
 			$this->EE->number->format($item->taxed_price_subtotal() - $item->price_subtotal() );
			
		$vars[$prefix.'item_price'] = 
		$vars[$prefix.'price'] = 
			$this->EE->number->format($vars[$prefix.'price_numeric']);
			
		$vars[$prefix.'item_price:plus_tax'] = 
		$vars[$prefix.'price:plus_tax'] = 
			$this->EE->number->format($item->taxed_price());
		
		$vars[$prefix.'item_shipping'] = 
		$vars[$prefix.'shipping'] = 
			$this->EE->number->format($item->shipping());
			
		$vars[$prefix.'item_weight'] = 
		$vars[$prefix.'weight'] = 
			$item->weight();
			
		$vars[$prefix.'item_base_price_numeric'] = 
		$vars[$prefix.'base_price_numeric'] = 
		$vars[$prefix.'item_base_price:numeric'] = 
		$vars[$prefix.'base_price:numeric'] = 
			$item->base_price();
		
		$vars[$prefix.'item_base_price_plus_tax_numeric'] = 
		$vars[$prefix.'base_price_plus_tax_numeric'] = 
		$vars[$prefix.'item_base_price_plus_tax:numeric'] = 
		$vars[$prefix.'base_price_plus_tax:numeric'] =  
			$item->taxed_base_price();

		$vars[$prefix.'item_base_price'] = 
		$vars[$prefix.'base_price'] = 
			$this->EE->number->format($item->base_price());
		
		$vars[$prefix.'item_base_price_plus_tax'] = $vars[$prefix.'base_price:plus_tax'] = $this->EE->number->format( $item->taxed_base_price() );

		$vars[$prefix.'item_discounted_price'] =
		$vars[$prefix.'discounted_price'] =
			$this->EE->number->format($item->discounted_price());

		$vars[$prefix.'item_discounted_subtotal'] =
		$vars[$prefix.'discounted_subtotal'] =
			$this->EE->number->format($item->discounted_price_subtotal());

		$vars[$prefix.'item_discounted_price_numeric'] =
		$vars[$prefix.'discounted_price_numeric'] =
		$vars[$prefix.'item_discounted_price:numeric'] =
		$vars[$prefix.'discounted_price:numeric'] =
			$item->discounted_price();

		$vars[$prefix.'item_discount'] =
		$vars[$prefix.'discount'] =
			$this->EE->number->format($item->discount());

		$vars[$prefix.'item_discount_numeric'] =
		$vars[$prefix.'discount_numeric'] =
		$vars[$prefix.'item_discount:numeric'] =
		$vars[$prefix.'discount:numeric'] =
			$item->discount();

		if ($item->discounts())
		{
			$vars[$prefix.'discounts'] = array();

			foreach ($item->discounts() as $registered_discount)
			{
				$vars[$prefix.'discounts'][] = array(
					'amount' => $this->EE->number->format($registered_discount->amount()),
					'amount:numeric' => $registered_discount->amount(),
					'reason' => $registered_discount->reason(),
					'coupon_code' => $registered_discount->coupon_code(),
				);
			}
		}
		else
		{
			$vars[$prefix.'discounts'] = array(array());
		}
		
		//@TODO better categories parsing, with tagparams, parse_variables, custom cat fields and path= vars
		//@TODO move this to entry_vars in cartthrob_entries_model
		if (empty($categories) || ! $product)
		{
			$vars[$prefix.'categories'] = array(array());
		}
		else
		{
			$vars[$prefix.'categories'] = array();
			
			foreach ($categories as $category)
			{
				if (in_array($category['category_id'], $product['categories']))
				{
					$vars[$prefix.'categories'][] = $category;
				}
			}
			
			if (count($vars[$prefix.'categories']) === 0)
			{
				$vars[$prefix.'categories'][] = array();
			}
		}
		
		if ( ! isset($vars[$prefix.'url_title']))
		{
			$vars[$prefix.'url_title'] = '';
		}
		
		foreach ($row_id_paths[0] as $i => $match)
		{
			$vars[substr($match, 1, -1)] = $this->EE->functions->create_url($row_id_paths[3][$i].'/'.$item->row_id());
		}
		
		return $vars;
	}
	
	public function sub_item_vars(Cartthrob_item $item, $global_vars = array(), $tagdata = FALSE)
	{
		$vars = array();
		
		$prefix = 'sub:';
		
		$count = 1;
		$total_results = count($item->sub_items());
		
		if ($item->sub_items())
		{
			foreach ($item->sub_items() as $sub_item)
			{
				$row = $this->item_vars($sub_item, $global_vars, $tagdata, $prefix);
				
				$row[$prefix.'row_id'] = $item->row_id().':'.$sub_item->row_id().":";
				$row[$prefix.'parent_id'] = $item->row_id();
				$row[$prefix.'child_id'] = $sub_item->row_id();
				
				$row[$prefix.'count'] = $count;
				$row[$prefix.'first_row'] = ($count === 1) ? TRUE : FALSE;
				$row[$prefix.'last_row'] = ($count === $total_results) ? TRUE : FALSE;
				
				//$row = array_merge($row, array_key_prefix($row, 'sub:'));
				
				$vars[] = $row;
				
				$count++;
			}
		}
		
		return $vars;
	}
	
	//use this with TMPL->parse_variables
	/**
	 * This parses the following tags: (in the case of the exp:cartthrob:item_options tag,)
	 *
	 * {item_options:field_name}
	 * 
	 * {item_options:field_name:price}
	 * 
	 * {item_options:field_name:price_numeric}
	 * 
	 * {item_options:field_name:option_name}
	 * 
	 * {item_options:field_name:options_exist}
	 * 
	 * {item_options:field_name:custom_column_name}
	 * 
	 * {item_options:select:field_name}
	 * 
	 * {item_options:input:field_name}
	 * 
	 * {item_options:select:field_name}
	 * 	{option}
	 * 	{option_value}
	 * 	{selected}
	 * 	{checked}
	 * 	{option_name}
	 * 	{price}
	 * 	{option_price}
	 * 	{price_numeric}
	 * 	{price:numeric}
	 * 	{price:plus_tax}
	 * 	{price_numeric:plus_tax}
	 * 	{price:plus_tax_numeric}
	 * 	{option_price:numeric}
	 * 	{option_price_numeric}
	 * 	{taxed_price}
	 * 	{option_taxed_price}
	 * 	{option_price:plus_tax}
	 * 	{option_price_plus_tax}
	 * 	{option_total_results}
	 * 	{option_first_row}
	 * 	{option_last_row}
	 * 	{option_count}
	 * 	{option_selected}
	 * 	{input_name}
	 * 	{option_field}
	 * 	{dynamic}
	 * {/item_options:select:field_name}
	 * 
	 * {item_options:options:field_name}
	 * 	{!--same as select--}
	 * {/item_options:options:field_name}
	 *
	 * In $field_name mode (triggered in exp:cartthrob:item_options for instance):
	 * 
	 * {item_options:select:field_name} simply becomes {select}, and so forth
	 * 
	 * @param int|false $entry_id   
	 * @param int|false $row_id     the cart item's row_id
	 * @param string|false $field_name (optional) specify the field_name, triggers $field_name mode, see above
	 * @param string|false $selected if set, overrides other possible selected values
	 * 
	 * @return Type    Description
	 */
	public function item_option_vars($entry_id = FALSE, $row_id = FALSE, $field_name = FALSE, $selected = FALSE)
	{
		$this->EE->load->model(array('cartthrob_entries_model', 'product_model'));
		
		$this->EE->load->helper(array('form', 'array'));
		
		$this->EE->load->library('number');
		
		$this->EE->load->library('api/api_cartthrob_tax_plugins');
		
		$vars = array();
		
		$price_modifiers = $this->EE->product_model->get_all_price_modifiers($entry_id);
		
		$item = FALSE;
		
		if (strpos($row_id, ':') !== FALSE)
		{
			$row_id_parts = explode(':', $row_id);
			
			if ($parent_item = $this->EE->cartthrob->cart->item($row_id_parts[0]))
			{
				$item = $parent_item->sub_item($row_id_parts[1]);
			}
		}
		else
		{
			$item = $this->EE->cartthrob->cart->item($row_id);
		}
		
		$prefix = '(item_options?:)';
		
		if ($field_name)
		{
			$prefix .= '?';
		}
		
		foreach ($this->EE->TMPL->var_pair as $var_name => $var_params)
		{
			$var_close_name = (strpos($var_name, ' ') !== FALSE) ? substr($var_name, 0, strpos($var_name, ' ')) : $var_name;
			
			if (preg_match('/^'.$prefix.'(select|list|options)(:[^\s]*)?/', $var_name, $match))
			{
				$select = ($match[2] === 'select');
				
				$var_params['name'] = ( ! empty($match[3])) ? substr($match[3], 1) : $field_name;
				
				if (preg_match_all("/".LD.preg_quote($var_name).RD."(.*?)".LD.'\/'.$var_close_name.RD."/s", $this->EE->TMPL->tagdata, $matches))
				{
					foreach ($matches[0] as $match_index => $full_match)
					{
						if (isset($var_params['entry_id']))
						{
							$price_modifiers = $this->EE->product_model->get_all_price_modifiers($var_params['entry_id'], $get_configurations = TRUE );
						}
						if (isset($price_modifiers))
						{
							foreach ($price_modifiers as $key => $value)
							{
								if (strpos($key, "configuration:") !== FALSE)
								{
									list($a, $field_name, $option_group) = explode(":", $key); 
									
									// we don't want the main field ALSO showing up. just he sub configuration:whatever fields. this removes the nice, simple main dropdown. 
									if (isset($price_modifiers[$field_name]))
									{
										unset($price_modifiers[$field_name]); 
									}
								}
							}
						}

						if ( ! empty($var_params['name']))
						{
							$output = '';
							
							$values = param_string_to_array(( ! empty($var_params['values'])) ? $var_params['values'] : '');
							
							if ( ! isset($price_modifiers[$var_params['name']]) && $item && $item->item_options($var_params['name']) && ! isset($values[$item->item_options($var_params['name'])]))
							{
								$values[$item->item_options($var_params['name'])] = $item->item_options($var_params['name']);
							}
							
							if (count($values))
							{
								$item_option_names = $this->EE->cartthrob->cart->meta('item_option_names');
								
								foreach ($values as $key => $value)
								{
									$item_option_names[$var_params['name']][$key] = $value;
								}
								
								$this->EE->cartthrob->cart->set_meta('item_option_names', $item_option_names);
							}
							
							if (isset($price_modifiers[$var_params['name']]))
							{
								foreach ($price_modifiers[$var_params['name']] as $option)
								{
									if (isset($option['option_value']) && isset($option['option_value']))
									{
										$values[$option['option_value']] = $option['option_name'];
										
										$prices[$var_params['name']][$option['option_value']] = (isset($option['price'])) ? $option['price'] : 0;

										$weights[$var_params['name']][$option['option_value']] = (isset($option['weight'])) ? $option['weight'] : 0;
										
										$columns[$option['option_value']] = $option;
									}
								}
							}
						
							$attrs = array();

							$extra = '';

							foreach ($var_params as $param_name => $param_value)
							{
								if ( ! $param_value)
								{
									continue;
								}
								
								if (preg_match('/attr:([a-zA-Z0-9_-]+)/', $param_name, $match))
								{
									$attrs[$match[1]] = $param_value;
								}
								else if (in_array($param_name, array('class', 'id', 'onchange')))
								{
									$attrs[$param_name] = $param_value;
								}
							}
							
							$extra = ($attrs) ? ' '._attributes_to_string($attrs) : '';

							if ($item)
							{
								$var_params['row_id'] = ($item->is_sub_item()) ? $item->parent_item()->row_id().':'.$item->row_id() : $item->row_id();
							}
							else if ($row_id !== FALSE)
							{
								$var_params['row_id'] = $row_id;
							}
						
							if ($var_params['name'] === 'quantity')
							{
								if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
								{
									$input_name = 'quantity['.$var_params['row_id'].']';
								}
								else
								{
									$input_name = 'quantity';
								}
							}
							else
							{
								if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
								{
									$input_name = 'item_options['.$var_params['row_id'].']['.$var_params['name'].']';
								}
								else
								{
									$input_name = 'item_options['.$var_params['name'].']';
								}
							}

							$var_pair_tagdata = $matches[1][$match_index];

							if ( ! isset($var_params['selected']))
							{
								$configurator_name = NULL; 
								if (strpos($var_params['name'], "configuration:") !== FALSE)
								{
									list($a, $configurator_name, $sub_field_name) = explode(":", $var_params['name']); 
								}
								
								if ($item &&  $configuration = $item->meta("configuration"))
								{
									$arr = element($configurator_name, $configuration); 
 									if ($arr && is_array($arr))
									{
 										foreach ($arr as $k => $v)
										{
 											if ($var_params['name'] == "configuration:".$configurator_name.":".$k)
											{
												$var_params['selected']  = $v; 
											}
										}
									}
									elseif ($item && $item->item_options($var_params['name']))
									{
										$var_params['selected'] = $item->item_options($var_params['name']);
									}
								}
								elseif ($item && $item->item_options($var_params['name']))
								{
									$var_params['selected'] = $item->item_options($var_params['name']);
								}
								else
								{
									$var_params['selected'] = NULL;
									if ($row_id !== FALSE && $selected !== FALSE)
									{
										$var_params['selected'] = $selected;
									}
								}
							}

							if ( ! isset($var_params['checked']))
							{
								if ($item && $item->item_options($var_params['name']))
								{
									$var_params['checked'] = $item->item_options($var_params['name']);
								}
								else
								{
									$var_params['checked'] = NULL;
								}
							}

 							// checking teh field type (if this is a configurator option)
							// if it is, then later we'll output a text field. 
							$ft = NULL; 
							if (!empty($price_modifiers[$var_params['name']][0]['field_type']))
							{
								$ft = $price_modifiers[$var_params['name']][0]['field_type']; 
							}
							
   							if (count($values) && $ft !="text" )
							{
								if ($select)
								{
									$output .= '<select name="'.$input_name.'"'.$extra.'>';
								}
								
								$var_pair_var_data = array();
								
								$count = 1;
								
								foreach ($values as $key => $value)
								{
									$price = isset($prices[$var_params['name']][$key]) ? $prices[$var_params['name']][$key] : '';
									$weight = isset($prices[$var_params['name']][$key]) ? $weights[$var_params['name']][$key] : '';

									// this attempts to get tax on the item option. may not work in all cases.. especially if the customer is using tax classes. 
									// do not get rid of this use of the API. it's appropriate here, because item_options are not items, and can't use item methods to calculate their own taxes
									// potentially the price could be slightly off, but there's not much we can really do about that
									$taxed_price = $price * (1 + $this->EE->api_cartthrob_tax_plugins->tax_rate());
									
									$row = array(
										'option' => $key,
										'option_value' => $key,
										'selected' => (isset($var_params['selected']) && $var_params['selected'] == $key) ? ' selected="selected"' : '',
										'checked' => (isset($var_params['checked']) && $var_params['checked'] == $key) ? ' checked="checked"' : '',
										'option_name' => $value,
										'price' => $this->EE->number->format($price),
										'weight' => $weight,
										'option_weight'=> $weight,
										'option_price' => $this->EE->number->format($price),
										'price_numeric' => $price,
										'price:numeric' => $price,
										'price:plus_tax' => $this->EE->number->format($taxed_price),
										'price_numeric:plus_tax' => $taxed_price,
										'price:plus_tax_numeric' => $taxed_price,
										'option_price:numeric' => $price,
										'option_price_numeric' => $price,
										'taxed_price' => $this->EE->number->format($taxed_price),
										'option_taxed_price' => $this->EE->number->format($taxed_price),
										'option_price:plus_tax' => $this->EE->number->format($taxed_price),
										'option_price_plus_tax' => $this->EE->number->format($taxed_price),
										'option_total_results' => count($values),
										'option_first_row' => (int) ($count === 1),
										'option_last_row' => (int) ($count === count($values)),
										'option_count' => $count++,
										'option_selected' => (int) ((isset($var_params['selected']) && $var_params['selected'] == $key)),
										'input_name' => $input_name,
										'option_field' => $var_params['name'],
										'dynamic' => (int) ( ! isset($price_modifiers[$var_params['name']])),
									);
									
									if (isset($columns[$key]))
									{
										$row = array_merge($columns[$key], $row);
									}
									$var_pair_var_data[] = $row;
								}
								
								$output .= $this->EE->TMPL->parse_variables($var_pair_tagdata, $var_pair_var_data);
								
								if ($select)
								{
									$output .= '</select>';
								}
							}
							elseif (count($values))
							{
								$selected = (isset($var_params['selected'])) ? ' value="'.$var_params['selected'].'" '  : ''; 
								$output .=  '<input type="text" name="'.$input_name.'"'.$extra. $selected.' />';
							}
							$vars[substr($matches[0][$match_index], 1, -1)] = $output;
						}
					}
				}
			}
		}
		
		foreach ($this->EE->TMPL->var_single as $var_name)
		{
			if (preg_match('/^'.$prefix.'select(:[^\s]+)?(\s+.*)?$/', $var_name, $match))
			{
				$var_string = element(3, $match);

				$var_params = $this->EE->functions->assign_parameters($var_string);

				if ( ! is_array($var_params))
				{
					$var_params = array();
				}

				$var_params['name'] = ( ! empty($match[2])) ? substr($match[2], 1) : $field_name;
				
				if (isset($var_params['entry_id']))
				{
					$price_modifiers = $this->EE->product_model->get_all_price_modifiers($var_params['entry_id']);
				}

				$values = param_string_to_array(( ! empty($var_params['values'])) ? $var_params['values'] : '');
				
				if ( ! isset($price_modifiers[$var_params['name']]) && $item && $item->item_options($var_params['name']) && ! isset($values[$item->item_options($var_params['name'])]))
				{
					$values[$item->item_options($var_params['name'])] = $item->item_options($var_params['name']);
				}

				if (count($values))
				{
					$item_option_names = $this->EE->cartthrob->cart->meta('item_option_names');
					
					foreach ($values as $key => $value)
					{
						$item_option_names[$var_params['name']][$key] = $value;
					}
					
					$this->EE->cartthrob->cart->set_meta('item_option_names', $item_option_names);
				}
				
				if (isset($price_modifiers[$var_params['name']]))
				{
					foreach ($price_modifiers[$var_params['name']] as $option)
					{
						$values[$option['option_value']] = $option['option_name'];
					}
				}
				
				if ( ! empty($var_params['name']))
				{
					$attrs = array();

					$extra = '';

					foreach ($var_params as $param_name => $param_value)
					{
						if ( ! $param_value)
						{
							continue;
						}
						
						if (preg_match('/attr:([a-zA-Z0-9_-]+)/', $param_name, $match))
						{
							$attrs[$match[1]] = $param_value;
						}
						else if (in_array($param_name, array('class', 'id', 'onchange')))
						{
							$attrs[$param_name] = $param_value;
						}
					}
					
					$extra = ($attrs) ? ' '._attributes_to_string($attrs) : '';

					if ($item)
					{
						$var_params['row_id'] = ($item->is_sub_item()) ? $item->parent_item()->row_id().':'.$item->row_id() : $item->row_id();
					}
					else if ($row_id !== FALSE)
					{
						$var_params['row_id'] = $row_id;
					}
					
					if ($var_params['name'] === 'quantity')
					{
						if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
						{
							$input_name = 'quantity['.$var_params['row_id'].']';
						}
						else
						{
							$input_name = 'quantity';
						}
					}
					else
					{
						if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
						{
							$input_name = 'item_options['.$var_params['row_id'].']['.$var_params['name'].']';
						}
						else
						{
							$input_name = 'item_options['.$var_params['name'].']';
						}
					}

					if ( ! isset($var_params['selected']))
					{
						if ($item && $item->item_options($var_params['name']))
						{
							$var_params['selected'] = $item->item_options($var_params['name']);
						}
						else
						{
							$var_params['selected'] = NULL;
						}
					}

					if ( ! isset($var_params['checked']))
					{
						if ($item && $item->item_options($var_params['name']))
						{
							$var_params['selected'] = $item->item_options($var_params['name']);
						}
						else
						{
							$var_params['selected'] = NULL;
						}
					}
					
					$vars[$var_name] = ($values) ? form_dropdown($input_name, $values, $var_params['selected'], $extra) : '';
				}
			}
			else if (preg_match('/^'.$prefix.'input(:[^\s]+)?(\s+.*)?$/', $var_name, $match))
			{
				$var_string = element(3, $match);
				
				$var_params = $this->EE->functions->assign_parameters($var_string);
				
				if ( ! is_array($var_params))
				{
					$var_params = array();
				}

				$var_params['name'] = ( ! empty($match[2])) ? substr($match[2], 1) : $field_name;

				if ( ! empty($var_params['name']))
				{
					if ($item)
					{
						$var_params['row_id'] = ($item->is_sub_item()) ? $item->parent_item()->row_id().':'.$item->row_id() : $item->row_id();
					}
					else if ($row_id !== FALSE)
					{
						$var_params['row_id'] = $row_id;
					}
					
					if ($var_params['name'] === 'quantity')
					{
						if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
						{
							$input_name = 'quantity['.$var_params['row_id'].']';
						}
						else
						{
							$input_name = 'quantity';
						}

						$var_params['value'] = ($item) ? $item->quantity() : ((isset($var_params['value']) ? $var_params['value'] : ''));
					}
					else
					{
						if (isset($var_params['row_id']) && $var_params['row_id'] !== '')
						{
							$input_name = 'item_options['.$var_params['row_id'].']['.$var_params['name'].']';
						}
						else
						{
							$input_name = 'item_options['.$var_params['name'].']';
						}

						$var_params['value'] = ($item) ? $item->item_options($var_params['name']) : ((isset($var_params['value']) ? $var_params['value'] : ''));
					}
					
					$attrs = array();

					$extra = '';

					foreach ($var_params as $param_name => $param_value)
					{
						if ( ! $param_value)
						{
							continue;
						}
						
						if (preg_match('/attr:([a-zA-Z0-9_-]+)/', $param_name, $match))
						{
							$attrs[$match[1]] = $param_value;
						}
						else if (in_array($param_name, array('value', 'class', 'id', 'onchange')))
						{
							$attrs[$param_name] = $param_value;
						}
					}
					
					$extra = ($attrs) ? ' '._attributes_to_string($attrs) : '';

					$type = ( ! empty($var_params['type'])) ? $var_params['type'] : 'text';

					if ( ! isset($var_params['selected']))
					{
						if ($item && $item->item_options($var_params['name']))
						{
							$var_params['selected'] = $item->item_options($var_params['name']);
						}
						else
						{
							$var_params['selected'] = NULL;
						}
					}

					$vars[$var_name] =  '<input type="'.$type.'" name="'.$input_name.'"'.$extra.' />';
				}
			}
			else if (preg_match('/^item_options?:(.*):option_name/', $var_name, $match))
			{
				$vars[$var_name] = '';
				
				if ($item && $item->item_options($match[1]) !== FALSE)
				{
					if (isset($price_modifiers[$match[1]]))
					{
						foreach ($price_modifiers[$match[1]] as $row)
						{
							if ($row['option_value'] === $item->item_options($match[1]))
							{
								$vars[$var_name] = $row['option_name'];
								break;
							}
						}
					}
					else
					{
						if ($item_option_names = $item->meta('item_option_names'))
						{
							foreach ($item_option_names as $option_value => $option_name)
							{
								if ($item->item_options($match[1]) === $option_value)
								{
									$vars[$var_name] = $option_name;
									break;
								}
							}
						}
					}
				}
			}
			else if (preg_match('/^item_options?:(.*):options_exist/', $var_name, $match))
			{
 				$vars[$var_name] = (isset($price_modifiers[$match[1]]) && count($price_modifiers[$match[1]]) > 0) ? (int) count($price_modifiers[$match[1]]) > 0 : FALSE;
				
			}
			else if (preg_match('/^item_options?:(.*):price([_:]numeric)?/', $var_name, $match))
			{
				$vars[$var_name] = '';
				
				if ($item && $item->item_options($match[1]) !== FALSE)
				{
					if (isset($price_modifiers[$match[1]]))
					{
						foreach ($price_modifiers[$match[1]] as $row)
						{
							if ($row['option_value'] === $item->item_options($match[1]))
							{
								$vars[$var_name] = ( ! empty($match[2])) ? $row['price'] : $this->EE->number->format($row['price']);
								break;
							}
						}
					}
				}
			}
			else if (preg_match('/^item_options?:(.*):(.*)/', $var_name, $match))
			{
				$vars[$var_name] = '';
				
				if ($item && $item->item_options($match[1]) !== FALSE)
				{
					if (isset($price_modifiers[$match[1]]))
					{
						foreach ($price_modifiers[$match[1]] as $row)
						{
							if ($row['option_value'] === $item->item_options($match[1]))
							{
								$vars[$var_name] = (isset($row[$match[2]])) ? $row[$match[2]] : '';
								break;
							}
						}
					}
				}
			}
			else if (preg_match('/^item_options?:(.*)/', $var_name, $match))
			{
				$vars[$var_name] = ($item) ? $item->item_options($match[1]) : '';
			}
		}
		
		return $vars;
	}
	
	public function set_global_values()
	{
		$this->EE->load->library(array('form_validation', 'form_builder'));
		
		$customer_info_keys = array_keys($this->EE->cartthrob->cart->customer_info());
		
		//set these so they get parsed, even though the real form input does not have the customer_ prefix
		foreach (array_values($customer_info_keys) as $key)
		{
			$_POST['customer_'.$key] = $this->EE->input->post($key);
			
			$customer_info_keys[] = 'customer_'.$key;
		}
		
		$this->EE->form_builder->set_value(array_merge(
			array(
				'custom_data',
				'language',
				'shipping',
				'shipping_option',
			),
			$customer_info_keys
		));
	}
	
	public function global_variables($add_form_variables = FALSE)
	{
		$this->EE->load->library('form_builder');
		
		static $static_variables;
		
		if (is_null($static_variables))
		{
			$static_variables = array_merge(
				$this->EE->cartthrob->cart->customer_info(),
				array_key_prefix($this->EE->cartthrob->cart->customer_info(), 'customer_'),
				array_key_prefix($this->EE->cartthrob->cart->custom_data(), 'custom_data:')
			);
		}
		$variables = array_merge($this->EE->cartthrob->cart->info(), $static_variables);
		
		if ($add_form_variables)
		{
			$this->EE->load->library('form_builder');
			
			$variables = array_merge($variables, $this->EE->form_builder->form_variables());
		}
		
		if (preg_match_all('/'.LD.'(custom_data:.*?)'.RD.'/', $this->EE->TMPL->tagdata, $matches))
		{
			foreach ($matches[1] as $i => $match)
			{
				if ( ! isset($variables[$match]))
				{
					$variables[$match] = '';
				}
			}
		}
 		return $variables;
	}
}